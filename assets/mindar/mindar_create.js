import 'aframe';
import '/build/mindar/mindar-image-aframe.prod.js';
import { Application } from '@hotwired/stimulus';
import MindarCreateController from '../controllers/mindar_create_controller';

const app = Application.start();
app.register('mindar-create', MindarCreateController);
