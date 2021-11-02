import './styles/app.scss';
import './bootstrap';
import {Tooltip, Toast, Modal} from 'bootstrap';
import noUiSlider from 'nouislider';

document.querySelector('body').addEventListener('click', (e) => {
  if (
    !document.querySelector('.navbar-collapse').contains(e.target) &&
    document.querySelector('.navbar-collapse').classList.contains('show')
  ) {
    e.preventDefault();
    document.querySelector('.navbar-collapse')
        .classList
        .remove('show');
  }
});

if (document.querySelector('body').classList.contains('purchase')) {
  const renderPaymentMethod = () => {
    if (document.getElementById('purchase_mode_0').checked) {
      document.getElementById('bank-wire')
          .classList
          .remove('d-none');
      document.getElementById('check')
          .classList
          .add('d-none');
    } else if (document.getElementById('purchase_mode_1').checked) {
      document.getElementById('bank-wire')
          .classList
          .add('d-none');
      document.getElementById('check')
          .classList
          .remove('d-none');
    } else {
      document.getElementById('bank-wire')
          .classList
          .add('d-none');
      document.getElementById('check')
          .classList
          .add('d-none');
    }
  };

  renderPaymentMethod();
  Array.from(document.querySelectorAll('input[name="purchase[mode]"]'))
      .forEach((e) => e.addEventListener('change', renderPaymentMethod));
}

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

if (document.querySelector('.address-professional')) {
  const addressProfessional = document.querySelector('.address-professional');
  const addressCompanyName = document.querySelector('.address-company-name');

  let companyName = addressCompanyName.querySelector('input').value;
  const collapseCompanyName = () => {
    if (parseInt(addressProfessional.value) === 0) {
      companyName = addressCompanyName.querySelector('input').value;
      addressCompanyName.classList.add('visually-hidden');
      addressCompanyName.querySelector('input').value = '';
    } else {
      addressCompanyName.classList.remove('visually-hidden');
      addressCompanyName.querySelector('input').value = companyName;
    }
  };
  collapseCompanyName();
  addressProfessional.addEventListener('change', collapseCompanyName);
}

if (document.querySelector('#access_client')) {
  const processAddresses = () => {
    if (document.querySelector('#access_manualDelivery_0').checked) {
      const address = JSON.parse(document.querySelector('#access_client option:checked').dataset.address);
      document.querySelector('#access_deliveryAddress_firstName').value = address.firstName;
      document.querySelector('#access_deliveryAddress_lastName').value = address.lastName;
      document.querySelector('#access_deliveryAddress_companyName').value = address.companyName;
      document.querySelector('#access_deliveryAddress_professional').value = address.professional ? 1 : 0;
      document.querySelector('#access_deliveryAddress_streetAddress').value = address.streetAddress;
      document.querySelector('#access_deliveryAddress_restAddress').value = address.restAddress;
      document.querySelector('#access_deliveryAddress_zipCode').value = address.zipCode;
      document.querySelector('#access_deliveryAddress_locality').value = address.locality;
      document.querySelector('#access_deliveryAddress_phone').value = address.phone;
      document.querySelector('#access_deliveryAddress_email').value = address.email;
    } else {
      const address = JSON.parse(document.querySelector('#delivery-addresses').value);
      document.querySelector('#access_deliveryAddress_firstName').value = address.firstName;
      document.querySelector('#access_deliveryAddress_lastName').value = address.lastName;
      document.querySelector('#access_deliveryAddress_companyName').value = address.companyName;
      document.querySelector('#access_deliveryAddress_professional').value = address.professional ? 1 : 0;
      document.querySelector('#access_deliveryAddress_streetAddress').value = address.streetAddress;
      document.querySelector('#access_deliveryAddress_restAddress').value = address.restAddress;
      document.querySelector('#access_deliveryAddress_zipCode').value = address.zipCode;
      document.querySelector('#access_deliveryAddress_locality').value = address.locality;
      document.querySelector('#access_deliveryAddress_phone').value = address.phone;
      document.querySelector('#access_deliveryAddress_email').value = address.email;
    }
  };

  processAddresses();

  document
      .querySelectorAll('#access_manualDelivery input')
      .forEach((e) => e.addEventListener('change', processAddresses));

  document
      .querySelectorAll('#access_manualDelivery input')
      .forEach((e) => e.addEventListener('change', (e) => {
        if (parseInt(e.currentTarget.value)) {
          document.querySelector('#delivery-addresses')
              .closest('.row')
              .classList.remove('show');
          document.querySelector('#address')
              .classList.add('show');
        } else {
          document.querySelector('#delivery-addresses')
              .closest('.row')
              .classList.add('show');
          document.querySelector('#address')
              .classList.remove('show');
        }
      }));

  document
      .querySelector('#access_client')
      .addEventListener('change', processAddresses);

  document
      .querySelector('#delivery-addresses')
      .addEventListener('change', processAddresses);
}

document.querySelectorAll('.btn-input-reset').forEach((btn) => {
  btn.addEventListener('click', () => {
    document.getElementById(btn.dataset.target).value = '';
    btn.closest('form').submit();
  });
});
