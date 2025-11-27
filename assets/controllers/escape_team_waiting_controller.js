import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static values = {
        progressUrl: String,
        playUrl: String,
        teamId: Number,
        pollInterval: { type: Number, default: 5000 },
    };

    static targets = ["status"];

    connect() {
        this._redirected = false;
        this._startPolling();
    }

    disconnect() {
        if (this._pollTimer) {
            clearInterval(this._pollTimer);
        }
    }

    _startPolling() {
        if (!this.progressUrlValue) return;

        this._pollTimer = setInterval(() => this._refresh(), this.pollIntervalValue);
        this._refresh();
    }

    async _refresh() {
        if (this._redirected || !this.progressUrlValue) return;

        try {
            const response = await fetch(this.progressUrlValue, { headers: { "X-Requested-With": "XMLHttpRequest" } });
            const data = await response.json();

            const status = data?.status || "";
            const team = Array.isArray(data?.teams)
                ? data.teams.find((t) => t.teamId === this.teamIdValue)
                : null;

            if (status === "running" && team) {
                this._redirected = true;
                window.location.href = this.playUrlValue;
                return;
            }

            if (this.hasStatusTarget) {
                const message = status === "stopped"
                    ? "Le jeu est momentanément arrêté par l’admin."
                    : "En attente du lancement…";
                this.statusTarget.textContent = message;
            }
        } catch (e) {
            // ignore network errors
        }
    }
}
