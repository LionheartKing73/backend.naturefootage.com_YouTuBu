// The following is client-side web application JavaScript.

// Run a setup script to ensure Aspera Connect
// once the page loads.
if (window.addEventListener) {
    window.addEventListener('load', ensureConnect, false);
} else {
    window.attachEvent('onload', ensureConnect);
}

function ensureConnect() {
    // Instantiate an AW.ConnectInstaller object.
    connectInstaller = new AW.ConnectInstaller('http://d3gcli72yxqn2z.cloudfront.net/connect/');
    // Call the init() method of the ConnectInstaller
    // object with the desired options.
    connectInstaller.init({
        connectReady : handleConnectReady,
        install: handleInstall,
        minVersion: '3.0'
    });
}

// Set callback functions registered
// with the init() method.

var handleConnectReady = function() {
    // We know Aspera Connect is ready for our use.
    // Enable our application with a theoretical setUp() function.
    //myApp.setUp()
    alert('Installed');
};

var handleInstallError = function() {
    // Called if an install error occurs. Display some text.
    alert("It looks like something went wrong with the installation.");
};

var handleInstallDismiss = function() {
    // Called if a user dimisses the installation
    alert("Connect install was dismissed.");
};

var handleInstall = function() {
    // Run the embedded installer if
    // an install is required.
    // The startExternalInstall() method could
    // easily work here, too.
    // The user will be asked to refresh their browser
    // after install.
    connectInstaller.startEmbeddedInstall({
        installError : handleInstallError,
        installDismiss : handleInstallDismiss,
        prompt : true
    });
};
