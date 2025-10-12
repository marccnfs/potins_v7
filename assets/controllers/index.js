// assets/controllers/index.js
import { Application } from "@hotwired/stimulus";

import CryptexController from "./cryptex_controller";
import PlayflowController from "./playflow_controller";
import QrGeoController from "./qr_geo_controller";
import QrPrintController from "./qr-print_controller";
import SliderPuzzleController from "./slider_puzzle_controller";
import LogicFormController from "./logic_form_controller";
import VideoQuizController from "./video_quiz_controller";
import HtmlMinController from "./html_min_controller";
import CopyController from "./copy_controller";
import HudController from "./hud_controller";
import ModalController from "./modal_controller";
import ToastController from "./toast_controller";
import HintsController from "./hints_controller";
import ConfettiController from "./confetti_controller";
import ShareController from "./share_controller";
import MenuController from "./menu_controller";
import FinaleController from "./finale_controller";
import WizardHintsController from "./wizard_hints_controller";
import BoardWeekController from "./board_week_controller";



window.Stimulus = Application.start();

Stimulus.register("cryptex", CryptexController);
Stimulus.register("playflow", PlayflowController);
Stimulus.register("qr-geo", QrGeoController);
Stimulus.register("qr-print", QrPrintController);
Stimulus.register("slider-puzzle", SliderPuzzleController);
Stimulus.register("logic-form", LogicFormController);
Stimulus.register("video-quiz", VideoQuizController);
Stimulus.register("html-min", HtmlMinController);
Stimulus.register("copy", CopyController);
Stimulus.register("hud", HudController);
Stimulus.register("modal", ModalController);
Stimulus.register("toast", ToastController);
Stimulus.register("hints", HintsController);
Stimulus.register("confetti", ConfettiController);
Stimulus.register("share", ShareController);
Stimulus.register("menu", MenuController);
Stimulus.register("finale", FinaleController);
Stimulus.register("wizard-hints", WizardHintsController);
Stimulus.register("board-week", BoardWeekController);
