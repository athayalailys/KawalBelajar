import './bootstrap';
import Alpine from 'alpinejs';
import tutorWizard from './tutor-wizard';

Alpine.data('tutorWizard', tutorWizard);

window.Alpine = Alpine;
Alpine.start();