import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = [
        "placeholder",
        "details",
        "qrImage",
        "answerWrapper",
        "answerImage",
        "answerLink",
        "directWrapper",
        "directLink",
        "generateButton",
        "printButton",
        "expires"
    ];

    static values = {
        fetchUrl: String,
        printUrl: String,
        fallbackMessage: String,
        readyMessage: String,
        generatedMessage: String,
        generatingMessage: String,
        errorMessage: String,
        printWarningMessage: String,
        expiresPattern: String
    };

    connect() {
        this.token = null;
        this.resetState();
    }

    resetState() {
        if (!this.hasFetchUrlValue || !this.fetchUrlValue) {
            this.showMessage(this.fallbackMessageValue || "");
            this.disableButton(this.generateButtonTarget);
            this.disableButton(this.printButtonTarget);
        } else {
            this.showMessage(this.readyMessageValue || "");
            this.enableButton(this.generateButtonTarget);
            this.disableButton(this.printButtonTarget);
        }

        if (this.hasDetailsTarget) {
            this.detailsTarget.hidden = true;
        }
    }

    showMessage(message) {
        if (!this.hasPlaceholderTarget) {
            return;
        }

        this.placeholderTarget.textContent = message;
        this.placeholderTarget.hidden = message === "";
    }

    enableButton(target) {
        if (target) {
            target.disabled = false;
        }
    }

    disableButton(target) {
        if (target) {
            target.disabled = true;
        }
    }

    async generate(event) {
        event.preventDefault();

        if (!this.hasFetchUrlValue || !this.fetchUrlValue) {
            this.showMessage(this.fallbackMessageValue || "");
            return;
        }

        const button = this.hasGenerateButtonTarget ? this.generateButtonTarget : event.currentTarget;
        this.disableButton(button);
        this.showMessage(this.generatingMessageValue || "");

        try {
            const response = await fetch(this.fetchUrlValue, {
                method: "POST",
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    Accept: "application/json"
                }
            });

            if (!response.ok) {
                throw new Error("Request failed");
            }

            const data = await response.json();
            this.applyData(data);
            this.showMessage(this.generatedMessageValue || "");
            this.enableButton(this.printButtonTarget);
        } catch (error) {
            console.error(error);
            this.showMessage(this.errorMessageValue || "");
        } finally {
            this.enableButton(this.generateButtonTarget);
        }
    }

    applyData(data) {
        if (!data || !data.qr || !data.token) {
            this.showMessage(this.errorMessageValue || "");
            return;
        }

        this.token = data.token;

        if (this.hasQrImageTarget) {
            this.qrImageTarget.src = data.qr;
        }

        if (this.hasDetailsTarget) {
            this.detailsTarget.hidden = false;
        }

        if (this.hasDirectWrapperTarget) {
            if (data.directUrl) {
                this.directWrapperTarget.hidden = false;
                if (this.hasDirectLinkTarget) {
                    this.directLinkTarget.href = data.directUrl;
                    this.directLinkTarget.textContent = data.directUrl;
                }
            } else {
                this.directWrapperTarget.hidden = true;
            }
        }

        if (this.hasAnswerWrapperTarget) {
            if (data.answerQr) {
                this.answerWrapperTarget.hidden = false;
                if (this.hasAnswerImageTarget) {
                    this.answerImageTarget.src = data.answerQr;
                }
                if (this.hasAnswerLinkTarget && data.answerUrl) {
                    this.answerLinkTarget.href = data.answerUrl;
                    this.answerLinkTarget.textContent = data.answerUrl;
                }
            } else {
                this.answerWrapperTarget.hidden = true;
            }
        }

        if (this.hasExpiresTarget) {
            const formatted = this.formatExpires(data.expiresAt);
            this.expiresTarget.textContent = formatted;
        }
    }

    formatExpires(value) {
        if (!value) {
            return "";
        }

        const date = new Date(value);
        if (Number.isNaN(date.getTime())) {
            return "";
        }

        const formattedTime = new Intl.DateTimeFormat(undefined, {
            hour: "2-digit",
            minute: "2-digit"
        }).format(date);

        if (this.hasExpiresPatternValue) {
            return this.expiresPatternValue.replace("__TIME__", formattedTime);
        }

        return formattedTime;
    }

    print(event) {
        event.preventDefault();

        if (!this.token) {
            this.showMessage(this.printWarningMessageValue || "");
            return;
        }

        if (!this.hasPrintUrlValue || !this.printUrlValue) {
            this.showMessage(this.fallbackMessageValue || "");
            return;
        }

        const url = this.printUrlValue.replace("__TOKEN__", encodeURIComponent(this.token));
        const win = window.open(url, "_blank", "noopener");
        if (!win) {
            console.warn("Unable to open print window");
        }
    }
}
