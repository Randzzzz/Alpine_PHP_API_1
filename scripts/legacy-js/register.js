async function registerUser(event) {
  event.preventDefault();

  const formData = new FormData(event.target);
  const data = Object.fromEntries(formData.entries());

  try {
    const response = await fetch("core/api.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        action: "register",
        username: data.username,
        first_name: data.first_name,
        last_name: data.last_name,
        password: data.password,
        confirm_password: data.confirm_password,
        superadmin_code: data.superadmin_code,
        role: "superadmin",
      }),
    });

    const result = await response.json();

    if (result.success) {
      showSuccess(result.message);
      setTimeout(() => {
        window.location.href = "login.php";
      }, 2000);
    } else {
      showError(`Registration failed: ${result.message}`);
    }
  } catch (error) {
    showError(`registration error: ${error.message}`);
  }
}
