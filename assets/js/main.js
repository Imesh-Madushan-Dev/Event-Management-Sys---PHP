// Custom JavaScript

// Enable all tooltips
document.addEventListener("DOMContentLoaded", function () {
  var tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
  );
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });

  // Auto-hide alerts after 5 seconds
  setTimeout(function () {
    var alerts = document.querySelectorAll(".alert");
    alerts.forEach(function (alert) {
      var bsAlert = new bootstrap.Alert(alert);
      bsAlert.close();
    });
  }, 5000);
});

// Like Event Function
function likeEvent(eventId) {
  fetch("/events/like_event.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: "event_id=" + eventId,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        const likeBtn = document.querySelector("#like-btn-" + eventId);
        const likeCount = document.querySelector("#like-count-" + eventId);

        if (data.liked) {
          likeBtn.classList.add("liked");
          likeBtn.innerHTML = '<i class="fas fa-heart"></i>';
        } else {
          likeBtn.classList.remove("liked");
          likeBtn.innerHTML = '<i class="far fa-heart"></i>';
        }

        if (likeCount) {
          likeCount.textContent = data.likes;
        }
      } else {
        alert(data.message || "An error occurred. Please try again.");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
    });
}

// Attend Event Function
function attendEvent(eventId) {
  fetch("/events/attend_event.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: "event_id=" + eventId,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        const attendBtn = document.querySelector("#attend-btn-" + eventId);
        const attendCount = document.querySelector("#attend-count-" + eventId);

        if (data.attending) {
          attendBtn.classList.add("attending");
          attendBtn.innerHTML = '<i class="fas fa-check-circle"></i>';
        } else {
          attendBtn.classList.remove("attending");
          attendBtn.innerHTML = '<i class="far fa-check-circle"></i>';
        }

        if (attendCount) {
          attendCount.textContent = data.attendees;
        }
      } else {
        alert(data.message || "An error occurred. Please try again.");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
    });
}

// Generate QR Code for Ticket
function generateQRCode(
  elementId,
  ticketCode,
  eventTitle = null,
  userName = null
) {
  if (!elementId || !ticketCode) return;

  const qrCodeElement = document.getElementById(elementId);
  if (!qrCodeElement) return;

  // Prepare QR data with enhanced information
  let qrData = ticketCode;

  // If additional data is provided, include it in the QR code
  if (eventTitle && userName) {
    qrData = JSON.stringify({
      ticket_code: ticketCode,
      event: eventTitle,
      user: userName,
    });
  }

  // Use a free QR code API
  const qrCodeUrl = `https://api.qrserver.com/v1/create-qr-code/?data=${encodeURIComponent(
    qrData
  )}&size=200x200`;

  // Create and append image
  const qrImage = document.createElement("img");
  qrImage.src = qrCodeUrl;
  qrImage.alt = "Ticket QR Code";
  qrImage.className = "img-fluid";

  // Clear existing content and append QR code image
  qrCodeElement.innerHTML = "";
  qrCodeElement.appendChild(qrImage);

  // Add ticket info below QR code if additional data is provided
  if (eventTitle && userName) {
    const infoDiv = document.createElement("div");
    infoDiv.className = "ticket-info mt-2 text-center";
    infoDiv.innerHTML = `
      <div class="fw-bold">${eventTitle}</div>
      <div class="text-muted small">Attendee: ${userName}</div>
      <div class="text-muted small">Code: ${ticketCode.substring(0, 8)}...</div>
    `;
    qrCodeElement.appendChild(infoDiv);
  }
}

// Delete confirmation
function confirmDelete(message, formId) {
  if (confirm(message || "Are you sure you want to delete this item?")) {
    document.getElementById(formId).submit();
  }
  return false;
}
