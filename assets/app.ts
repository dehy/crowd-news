/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.scss';

import 'bootstrap';
import { library, dom } from '@fortawesome/fontawesome-svg-core';
import { faHome, faNewspaper, faEnvelopesBulk, faList, faSquarePlus, faClockRotateLeft, faArchive, faUser, faUsers, faGears, faRightFromBracket } from '@fortawesome/free-solid-svg-icons';
//import { far } from '@fortawesome/free-regular-svg-icons'
//import { fab } from '@fortawesome/free-brands-svg-icons'

library.add(faHome, faNewspaper, faEnvelopesBulk, faList, faSquarePlus, faClockRotateLeft, faArchive, faUser, faUsers, faGears, faRightFromBracket);

dom.i2svg().then();

// start the Stimulus application
import './bootstrap';
