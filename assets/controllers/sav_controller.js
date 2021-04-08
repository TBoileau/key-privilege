import {Controller} from 'stimulus';

/**
 * Class SAV Controller for dropzone
 */
export default class extends Controller {
  /**
   * Create controller
   * @param {Context} props
   */
  constructor(props) {
    super(props);
    this.index = 0;
    this.container = document.getElementById('sav_attachments');
  }

  /**
   * Register events
   */
  connect() {
    this.element.addEventListener('dropzone:connect', this._onConnect);
    this.element.addEventListener('dropzone:change', this._onChange.bind(this));
    this.element.addEventListener('dropzone:clear', this._onClear);
  }

  /**
   * Remove {CustomEvent} events
   */
  disconnect() {
    this.element.removeEventListener('dropzone:connect', this._onConnect);
    this.element.removeEventListener('dropzone:change', this._onChange);
    this.element.removeEventListener('dropzone:clear', this._onClear);
  }

  /**
   * Listener on connect event
   * @private
   * @param {CustomEvent} event
   */
  _onConnect(event) {
  }

  /**
   * Listener on change event
   * @param {CustomEvent} event
   * @private
   */
  _onChange(event) {
    const data = new FormData();
    data.append('file', event.detail);
    fetch('/sav/upload',
        {
          method: 'POST',
          headers: {
            Accept: 'application/json',
          },
          body: data,
        })
        .then((res) => res.json())
        .then((json) => {
          const item = document.createElement('div');
          item.classList.add(
              'list-group-item',
              'd-flex',
              'justify-content-start',
              'align-items-center',
          );

          const input = document.createElement('input');
          input.setAttribute('type', 'hidden');
          input.setAttribute('name', `sav[attachments][${this.index}]`);
          input.setAttribute('value', json.file);
          item.appendChild(input);

          const img = document.createElement('img');
          img.setAttribute('src', '/' + json.file);
          img.setAttribute('alt', json.name);
          img.setAttribute('width', '50px');
          img.classList.add('me-3');
          item.appendChild(img);

          const span = document.createElement('span');
          span.textContent = json.name;
          item.appendChild(span);

          const link = document.createElement('a');
          link.setAttribute('href', '#');
          link.innerHTML = `<span class="fas fa-times"></span>`;
          link.classList.add('ms-auto', 'text-danger');
          item.appendChild(link);

          this.container.appendChild(item);
          this.index++;

          link.addEventListener('click', () => item.remove());

          const event = document.createEvent('HTMLEvents');
          event.initEvent('click', true, true);
          event.eventName = 'click';
          document
              .querySelector('.dropzone-preview-button')
              .dispatchEvent(event);
        });
  }

  /**
   * Listener on clear event
   * @param {CustomEvent} event
   * @private
   */
  _onClear(event) {
  }
}
