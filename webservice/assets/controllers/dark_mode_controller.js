import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static classes = ['light', 'dark'];
    static values = { colorScheme: {type: String, default: 'auto' }};
  
    initialize() {
      if ('colorScheme' in localStorage) {
        this.colorSchemeValue = localStorage.colorScheme;
      }
    
      // to listen for "system setting" changes
      window
        .matchMedia('(prefers-color-scheme: dark)')
        .addEventListener('change', this.#dispatchChange);
    }

    selectTargetConnected(target) {
      target.value = this.colorSchemeValue;
    }
  
    toggle(event) {
      this.colorSchemeValue = event.target.value;
    }
  
    async colorSchemeValueChanged() {
      localStorage.colorScheme = this.colorSchemeValue;
  
      // wait for next frame
      await new Promise(requestAnimationFrame);
  
      this.#dispatchChange();
    }
  
    updateColorScheme(event) {
      const { colorScheme } = event.detail;

      document.documentElement.setAttribute(
          'data-bs-theme',
          this.#isDark(colorScheme) ? 'dark' : 'light'
      );
    }
  
    #isDark(colorScheme) {
      if (colorScheme === 'auto') {
        return window.matchMedia('(prefers-color-scheme: dark)').matches;
      }
  
      return colorScheme === 'dark';
    }
  
    #dispatchChange = () => {
      this.dispatch('change', { detail: { colorScheme: this.colorSchemeValue } });
    };
  }
  
