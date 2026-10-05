importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-messaging.js');
firebase.initializeApp({
    apiKey: "AIzaSyDRMdb6i-_K3BYgVUQqoywmj2MizBJTVXA",
    authDomain: "mmc-motor.firebaseapp.com",
    projectId: "mmc-motor",
    storageBucket: "mmc-motor.firebasestorage.app",
    messagingSenderId: "96013657996",
    appId: "1:96013657996:web:68f2a8ca20a538ff77d8a9",
    measurementId: ""
});
const messaging = firebase.messaging();
messaging.setBackgroundMessageHandler(function (payload) {
    return self.registration.showNotification(payload.data.title, {
        body: payload.data.body ? payload.data.body : '',
        icon: payload.data.icon ? payload.data.icon : ''
    });
});