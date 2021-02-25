import './styles/app.scss';
import './bootstrap';
import { Toast } from 'bootstrap';

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

Array.from(document.querySelectorAll(".toast")).map(toast => (new Toast(toast)).show());
