// Import the functions you need from the SDKs you need
import { initializeApp } from "firebase/app";
import { getAnalytics } from "firebase/analytics";
// TODO: Add SDKs for Firebase products that you want to use
// https://firebase.google.com/docs/web/setup#available-libraries

// Your web app's Firebase configuration
// For Firebase JS SDK v7.20.0 and later, measurementId is optional
const firebaseConfig = {
  apiKey: "AIzaSyAz9YMFhbOhtYS_gdMs7gfwD2bLa_OX4q8",
  authDomain: "joshy-lucas.firebaseapp.com",
  projectId: "joshy-lucas",
  storageBucket: "joshy-lucas.firebasestorage.app",
  messagingSenderId: "55072201156",
  appId: "1:55072201156:web:5b5ac1a92039c78f79ec91",
  measurementId: "G-WP896LGVJQ"
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const analytics = getAnalytics(app);