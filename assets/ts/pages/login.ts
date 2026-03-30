document.addEventListener("DOMContentLoaded", () => {
    const toggleBtn = document.querySelector(".login-toggle-password");
    if (!toggleBtn) return;

    const input = document.getElementById("pwd") as HTMLInputElement | null;
    const icon = toggleBtn.querySelector("i");
    if (!input || !icon) return;

    toggleBtn.addEventListener("click", () => {
        const isPassword = input.type === "password";
        input.type = isPassword ? "text" : "password";
        icon.classList.toggle("fa-eye-slash", !isPassword);
        icon.classList.toggle("fa-eye", isPassword);
    });
});
