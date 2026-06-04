/**
 * NeuralPress - Front Client Bootloader & Controller
 */

document.addEventListener('DOMContentLoaded', () => {
    // Check if UI and API libraries exist and boot them safely
    if (window.NeuralPressUI) {
        window.NeuralPressUI.init();
        console.log("NeuralPress - Client interface nodes successfully connected.");
    }
});
