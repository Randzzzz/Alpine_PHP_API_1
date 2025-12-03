async function loginUser(event) {
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
        action: "login",
        username: data.username,
        password: data.password,
      }),
    });

    const result = await response.json();

    if (result.success) {
      showSuccess(result.message);
      setTimeout(() => {
        window.location.href = "dashboard.php";
      }, 2000);
    } else {
      showError(`Login failed: ${result.message}`);
    }
  } catch (error) {
    showError(`Login error: ${error.message}`);
  }
}
