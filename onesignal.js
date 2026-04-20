const ONESIGNAL_APP_ID = "c9fbc0bd-6561-433e-8782-c64f909e6f0c";

window.OneSignalDeferred = window.OneSignalDeferred || [];

const script = document.createElement("script");
script.src = "https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js";
script.defer = true;
document.head.appendChild(script);

OneSignalDeferred.push(async function (OneSignal) {
    await OneSignal.init({
        appId: ONESIGNAL_APP_ID,
        promptOptions: {
            slidedown: {
                prompts: [
                    {
                        type: "push",
                        autoPrompt: true,
                        text: {
                            actionMessage: "Silver Happy souhaite vous envoyer des rappels.",
                            acceptButton: "Autoriser",
                            cancelButton: "Non merci"
                        }
                    }
                ]
            }
        },
        allowLocalhostAsSecureOrigin: true
    });
});

async function envoyerNotification(message, id_senior) {
    try {
        const res = await fetch("/go-api/api/notifications", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                message: message,
                id_senior: id_senior
            })
        });

        const data = await res.json();
        console.log(data);
    } catch (e) {
        console.log("Erreur notification");
    }
}