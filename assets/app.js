import './styles/app.scss';
import './bootstrap';
import bootstrap from 'bootstrap';
import noUiSlider from 'nouislider';

Array.from(document.querySelectorAll(".input-group-password")).forEach(e => {
    let button = e.querySelector("button");
    let input = e.querySelector("input");
    let state = {
        password: "text",
        text: "password"
    };
    button.addEventListener("click", () => {
        input.setAttribute("type", state[input.getAttribute("type")]);
    });
});

Array.from(document.querySelectorAll(".toast")).map(toast => (new bootstrap.Toast(toast)).show());

Array.from(document.querySelectorAll(".slider")).map(slider => {
    let min = parseInt(slider.dataset.min);
    let max = parseInt(slider.dataset.max);
    let minTarget = document.querySelector(slider.dataset.minTarget);
    let maxTarget = document.querySelector(slider.dataset.maxTarget);
    noUiSlider.create(slider, {
        start: [parseInt(minTarget.value), parseInt(maxTarget.value)],
        tooltips: true,
        connect: true,
        step: 5,
        range: {
            'min': min,
            'max': max
        },
        format: {
            to: value => parseInt(value),
            from: value => parseInt(value)
        }
    }).on('update', function (values, handle) {
        let value = values[handle];
        if (handle) {
            maxTarget.value = value;
        } else {
            minTarget.value = value;
        }
    });
});
