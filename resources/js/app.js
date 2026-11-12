import './bootstrap';
import Alpine from 'alpinejs';
import tutorWizard from './tutor-wizard';
import registerStudentPortal from './student-portal';

Alpine.data('tutorWizard', tutorWizard);
registerStudentPortal(Alpine);

window.Alpine = Alpine;
Alpine.start();
