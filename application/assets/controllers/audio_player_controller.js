import { Controller } from '@hotwired/stimulus';
import WaveSurfer from 'wavesurfer.js';

// Module-level registry — ensures only one player plays at a time across all instances
const activeControllers = new Set();

export default class extends Controller {
    static values = {
        previewUrl: String,
        waveformUrl: String,
    };

    static targets = ['playIcon', 'pauseIcon', 'waveform'];

    connect() {
        this._wavesurfer = null;
        this._isPlaying = false;
        this._initialized = false;
        activeControllers.add(this);
    }

    disconnect() {
        activeControllers.delete(this);
        if (this._wavesurfer) {
            this._wavesurfer.destroy();
            this._wavesurfer = null;
        }
    }

    async toggle() {
        if (!this._initialized) {
            await this._init();
            this._initialized = true;
        }

        if (this._isPlaying) {
            this._wavesurfer.pause();
            this._setPlaying(false);
        } else {
            // Pause all other active instances first
            for (const ctrl of activeControllers) {
                if (ctrl !== this && ctrl._isPlaying) {
                    ctrl._wavesurfer.pause();
                    ctrl._setPlaying(false);
                }
            }
            this._wavesurfer.play();
            this._setPlaying(true);
        }
    }

    async _init() {
        // Fetch JSON peaks before creating WaveSurfer so the waveform renders immediately
        let peaks = null;
        if (this.waveformUrlValue) {
            try {
                const res = await fetch(this.waveformUrlValue);
                if (res.ok) {
                    const json = await res.json();
                    peaks = json.data ?? null;
                }
            } catch (_) {
                // Waveform fetch failed — WaveSurfer will decode from audio instead
            }
        }

        const options = {
            container: this.waveformTarget,
            url: this.previewUrlValue,
            waveColor: '#15C39A',
            progressColor: '#0a6b52',
            cursorColor: 'transparent',
            height: 32,
            normalize: true,
            barWidth: 2,
            barGap: 1,
            barRadius: 2,
        };

        // In WaveSurfer v7, passing peaks renders the waveform without decoding the audio.
        // The audio file is only fetched on .play(). peaks must be wrapped as an array of channels.
        if (peaks) {
            options.peaks = [peaks];
        }

        this._wavesurfer = WaveSurfer.create(options);

        this._wavesurfer.on('finish', () => {
            this._setPlaying(false);
        });

        this._wavesurfer.on('ready', () => {
            if (this.hasWaveformTarget) {
                this.waveformTarget.classList.remove('opacity-0');
            }
        });
    }

    _setPlaying(playing) {
        this._isPlaying = playing;
        if (this.hasPlayIconTarget) {
            this.playIconTarget.classList.toggle('hidden', playing);
        }
        if (this.hasPauseIconTarget) {
            this.pauseIconTarget.classList.toggle('hidden', !playing);
        }
    }
}
