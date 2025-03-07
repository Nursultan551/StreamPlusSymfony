<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;
use App\Entity\Address;
use App\Entity\Payment;
use App\Enum\SubscriptionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class OnboardingController extends AbstractController
{
    /**
     * Render the onboarding wizard page.
     */
    #[Route('/onboarding', name: 'onboarding')]
    public function index(): Response
    {
        return $this->render('onboarding/wizard.html.twig', [
            'controller_name' => 'OnboardingController',
        ]);
    }

    /**
     * Handle the creation of a new user with associated address and payment (if applicable).
     */
    #[Route('/onboarding/create', name: 'onboarding_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        // Validate CSRF token from request headers.
        $csrfToken = $request->headers->get('X-CSRF-Token');
        // TODO: move Token into env variables
        if (!$this->isCsrfTokenValid('lwc4BXr38febHrOLrQmmA6UOzBa1wH72', $csrfToken)) {
            return new JsonResponse(['error' => 'Invalid CSRF token'], 403);
        }

        // Decode the JSON payload and extract wizard data.
        $data = json_decode($request->getContent(), true);
        $wizardData = $data['wizard'] ?? [];

        // Create a new User entity and set properties.
        $user = new User();
        $user->setName($wizardData['name'] ?? '');
        $user->setEmail($wizardData['email'] ?? '');
        $user->setPhone($wizardData['phone'] ?? '');

        // Convert string to SubscriptionType enum if provided.
        if (!empty($wizardData['subscriptionType'])) {
            $subscriptionType = SubscriptionType::from($wizardData['subscriptionType']);
            $user->setSubscriptionType($subscriptionType);
        }

        // Validate the User entity.
        $violations = $validator->validate($user);
        $errors = [];
        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $fieldName = $violation->getPropertyPath();
                $errors[$fieldName][] = $violation->getMessage();
            }
            return new JsonResponse(['errors' => $errors], 422);
        }

        // Persist the User entity.
        $em->persist($user);

        // Create and persist Address entity with provided address data.
        $address = new Address();
        $address->setAddressLine1($wizardData['addressLine1'] ?? '');
        $address->setAddressLine2($wizardData['addressLine2'] ?? '');
        $address->setCity($wizardData['city'] ?? '');
        $address->setPostalCode($wizardData['postalCode'] ?? '');
        $address->setState($wizardData['state'] ?? '');
        $address->setCountry($wizardData['country'] ?? '');
        $address->setUser($user);

        $em->persist($address);

        // If subscription is premium, create and persist Payment entity.
        if ($user->getSubscriptionType() === SubscriptionType::PREMIUM) {
            $payment = new Payment();
            $payment->setCreditCardNumber($wizardData['creditCardNumber'] ?? '');
            $payment->setExpirationDate($wizardData['expirationDate'] ?? '');
            $payment->setCvv($wizardData['cvv'] ?? '');
            $payment->setUser($user);

            $em->persist($payment);
        }

        // Save all changes to the database.
        $em->flush();

        // Return success response with new user ID.
        return new JsonResponse([
            'status' => 'ok',
            'userId' => $user->getId(),
        ], 201);
    }

    /**
     * Validate individual steps of the onboarding process.
     */
    #[Route('/onboarding/validate-step', name: 'onboarding_validate_step', methods: ['POST'])]
    public function validateStep(Request $request, ValidatorInterface $validator): JsonResponse
    {
        // Decode incoming JSON payload.
        $payload = json_decode($request->getContent(), true);
        $stepIndex = $payload['step'] ?? 0;
        $wizardData = $payload['wizard'] ?? [];

        $errorsList = [];

        switch ($stepIndex) {
            case 0:
                // Validate user details (name, email, phone, subscription type).
                $user = new User();
                $user->setName($wizardData['name'] ?? '');
                $user->setEmail($wizardData['email'] ?? '');
                $user->setPhone($wizardData['phone'] ?? '');

                if (!empty($wizardData['subscriptionType'])) {
                    try {
                        $subscriptionType = SubscriptionType::from($wizardData['subscriptionType']);
                        $user->setSubscriptionType($subscriptionType);
                    } catch (\ValueError $e) {
                        $errorsList['subscriptionType'][] = 'Invalid subscription type.';
                    }
                }
                $violations = $validator->validate($user);
                foreach ($violations as $violation) {
                    $errorsList[$violation->getPropertyPath()][] = $violation->getMessage();
                }
                break;

            case 1:
                // Validate address details.
                $address = new Address();
                $address->setAddressLine1($wizardData['addressLine1'] ?? '');
                $address->setAddressLine2($wizardData['addressLine2'] ?? '');
                $address->setCity($wizardData['city'] ?? '');
                $address->setPostalCode($wizardData['postalCode'] ?? '');
                $address->setState($wizardData['state'] ?? '');
                $address->setCountry($wizardData['country'] ?? '');

                $violations = $validator->validate($address);
                foreach ($violations as $violation) {
                    $errorsList[$violation->getPropertyPath()][] = $violation->getMessage();
                }
                break;

            case 2:
                // Validate payment information.
                $payment = new Payment();
                $payment->setCreditCardNumber($wizardData['creditCardNumber'] ?? '');
                $payment->setExpirationDate($wizardData['expirationDate'] ?? '');
                $payment->setCvv($wizardData['cvv'] ?? '');

                $violations = $validator->validate($payment);
                foreach ($violations as $violation) {
                    $errorsList[$violation->getPropertyPath()][] = $violation->getMessage();
                }
                break;

            default:
                // For steps beyond defined validations, no checks are required.
                break;
        }

        // Return errors if any validation failures occurred.
        if (count($errorsList) > 0) {
            return new JsonResponse(['errors' => $errorsList], 422);
        }

        // Return success response.
        return new JsonResponse(['status' => 'ok']);
    }
}
