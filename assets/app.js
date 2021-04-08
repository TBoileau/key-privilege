import './styles/app.scss';
import './bootstrap';
import {Tooltip, Toast, Modal} from 'bootstrap';
import noUiSlider from 'nouislider';

Array.from(document.querySelectorAll('.modal-onload'))
    .map((e) => new Modal(e, {backdrop: false}))
    .forEach((modal) => modal.show());

Array.from(document.querySelectorAll('[data-bs-toggle=tooltip]'))
    .map((e) => new Tooltip(e));

Array.from(document.querySelectorAll('.input-group-password'))
    .forEach((e) => {
      const button = e.querySelector('button');
      const input = e.querySelector('input');
      const state = {
        password: 'text',
        text: 'password',
      };
      button.addEventListener('click', () => {
        input.setAttribute('type', state[input.getAttribute('type')]);
      });
    });

Array.from(document.querySelectorAll('.toast'))
    .map((toast) => (new Toast(toast)).show());

Array.from(document.querySelectorAll('.slider'))
    .map((slider) => {
      const min = parseInt(slider.dataset.min);
      const max = parseInt(slider.dataset.max);
      const minTarget = document.querySelector(slider.dataset.minTarget);
      const maxTarget = document.querySelector(slider.dataset.maxTarget);
      noUiSlider.create(slider, {
        start: [parseInt(minTarget.value), parseInt(maxTarget.value)],
        tooltips: true,
        connect: true,
        step: 5,
        range: {
          'min': min,
          'max': max,
        },
        format: {
          to: (value) => parseInt(value),
          from: (value) => parseInt(value),
        },
      }).on('update', function(values, handle) {
        const value = values[handle];
        if (handle) {
          maxTarget.value = value;
        } else {
          minTarget.value = value;
        }
      });
    });

const sidebarTogglers = Array.from(
    document.querySelectorAll('.sidebar-toggler'),
);

sidebarTogglers.map((toggler) => toggler.addEventListener('click', () => {
  document.querySelector('body').classList.toggle('sidebar-open');
  sidebarTogglers.map((e) => e
      .setAttribute('aria-expanded', document.querySelector('body')
          .classList
          .contains('sidebar-open')));
}));
