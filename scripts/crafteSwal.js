const crafteSwal = Swal.mixin({
  background: "#ffffff",
  color: "#324149",
  confirmButtonColor: "#324149",
  cancelButtonColor: "#6e7a86",
  customClass: {
    popup: " shadow-lg rounded-3",
    title: "fw-bold",
    confirmButton: "fw-semibold",
  },
});

function showSuccess(message) {
  crafteSwal.fire({
    icon: "success",
    title: "Success",
    text: message,
    timer: 2000,
    showConfirmButton: false,
  });
}

function showError(message) {
  crafteSwal.fire({
    icon: "error",
    title: "Error",
    text: message,
  });
}

function showConfirmPayment(totalAmount, cashAmount, change) {
  return crafteSwal.fire({
    icon: "question",
    title: "Confirm Payment",
    html: `
    <p class="text-lg mb-2">Total: <b>₱${totalAmount}</b></p>
    <p class="text-lg mb-2">Cash Received: <b>₱${cashAmount}</b></p>
    <p class="text-lg">Change: <b>₱${change}</b></p>
    `,
    showCancelButton: true,
    confirmButtonText: "Confirm",
    cancelButtonText: "Cancel",
  });
}

function showPaymentSuccess(result, change) {
  crafteSwal.fire({
    icon: "success",
    title: "Payment Successful",
    html: `
    <p class="text-lg">Transaction ID: <b>${result.transactionId}</b></p>
    <p class="text-lg">Change: <b>₱${change}</b></p>
    `,
    confirmButtonText: "Close",
  });
}
