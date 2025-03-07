import '@symfony/ux-turbo';
import { startStimulusApp } from '@symfony/stimulus-bridge';

const app = startStimulusApp(require.context('./controllers', true, /\.js$/));
// register any custom, 3rd party controllers here
// app.register('form_wizard_controller', SomeImportedController);
export { app };
