

import Alpine from 'alpinejs';
import { initAnimations } from './animations.js';

window.Alpine = Alpine;

Alpine.start();

document.addEventListener('DOMContentLoaded', initAnimations);
