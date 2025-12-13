// ===== Custom Notification System =====
function showNotification(message, type = "success", duration = 3000) {
  // Create notification container if it doesn't exist
  let notificationContainer = document.getElementById("notification-container");
  if (!notificationContainer) {
    notificationContainer = document.createElement("div");
    notificationContainer.id = "notification-container";
    notificationContainer.style.cssText = `
            position: fixed;
            top: 100px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
        `;
    document.body.appendChild(notificationContainer);
  }

  // Create notification element
  const notification = document.createElement("div");
  const bgColor =
    type === "success" ? "#4caf50" : type === "error" ? "#f44336" : "#2196F3";
  const icon = type === "success" ? "✓" : type === "error" ? "✕" : "ℹ";

  notification.style.cssText = `
        background-color: ${bgColor};
        color: white;
        padding: 16px 20px;
        margin-bottom: 12px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 15px;
        font-weight: 500;
        animation: slideIn 0.3s ease-out;
    `;

  notification.innerHTML = `
        <span style="font-size: 20px; font-weight: bold;">${icon}</span>
        <span>${message}</span>
    `;

  notificationContainer.appendChild(notification);

  // Auto-remove notification
  setTimeout(() => {
    notification.style.animation = "slideOut 0.3s ease-in";
    setTimeout(() => notification.remove(), 300);
  }, duration);
}

// Add animation styles
const style = document.createElement("style");
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

document.addEventListener("DOMContentLoaded", function () {
  // --- Reusable Logout Modal Logic ---
  const logoutLink = document.getElementById("logout-link");
  const modalContainer = document.getElementById("logout-modal-container");

  if (logoutLink && modalContainer) {
    fetch("../../components/logout_modal.html")
      .then((response) => {
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.text();
      })
      .then((html) => {
        modalContainer.innerHTML = html;
        const logoutModal = document.getElementById("logout-modal");
        const cancelLogout = document.getElementById("cancel-logout");

        if (logoutModal && cancelLogout) {
          logoutLink.addEventListener("click", (e) => {
            e.preventDefault();
            logoutModal.classList.add("show");
          });
          cancelLogout.addEventListener("click", () =>
            logoutModal.classList.remove("show")
          );
          logoutModal.addEventListener("click", (e) => {
            if (e.target === logoutModal) logoutModal.classList.remove("show");
          });
        }
      })
      .catch((error) => console.error("Error loading logout modal:", error));
  }

  // --- Calendar Logic ---
  const calendarEl = document.getElementById("calendar");
  if (calendarEl) {
    window.calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: "dayGridMonth",
      headerToolbar: {
        left: "prev,next today",
        center: "title",
        right: "dayGridMonth,timeGridWeek,listWeek",
      },
      googleCalendarApiKey: window.GOOGLE_API_KEY || "",
      eventSources: [
        {
          googleCalendarId:
            "en.philippines#holiday@group.v.calendar.google.com",
          className: "gcal-event",
        },
        {
          url: "../../server/api/events.php?action=get",
          failure: function () {
            console.error("Error fetching custom events");
          },
        },
      ],
      editable: true,
      selectable: true,
      dateClick: function (info) {
        // Open add event modal instead of using prompt
        const addEventModal = document.getElementById("add-event-modal");
        const addEventForm = document.getElementById("add-event-form");

        // Set the default date in the form
        document.getElementById("add-event-start").value = info.dateStr;
        document.getElementById("add-event-end").value = info.dateStr;

        // Show modal
        addEventModal.style.display = "flex";

        // Clear form
        addEventForm.reset();
        document.getElementById("add-event-start").value = info.dateStr;
        document.getElementById("add-event-end").value = info.dateStr;
        document.getElementById("add-event-published").checked = true;
      },
    });
    window.calendar.render();
  }

  // --- Manage Events Modal Logic ---
  const addEventBtn = document.getElementById("add-event-btn");
  const manageEventsBtn = document.getElementById("manage-events-btn");
  const manageEventsModal = document.getElementById("manage-events-modal");
  const closeManageEventsBtn = document.getElementById(
    "close-manage-events-btn"
  );
  const addEventModal = document.getElementById("add-event-modal");
  const addEventForm = document.getElementById("add-event-form");
  const addEventCancelBtn = document.getElementById("add-event-cancel-btn");
  const editEventModal = document.getElementById("edit-event-modal");
  const editEventForm = document.getElementById("edit-event-form");
  const editEventCancelBtn = document.getElementById("edit-event-cancel-btn");

  // --- Add Event Button Handler (from button click) ---
  if (addEventBtn) {
    addEventBtn.addEventListener("click", () => {
      // Set today as default date
      const today = new Date().toISOString().split("T")[0];
      document.getElementById("add-event-title").value = "";
      document.getElementById("add-event-start").value = today;
      document.getElementById("add-event-end").value = today;
      document.getElementById("add-event-type").value = "other";
      document.getElementById("add-other-type").value = "";
      document.getElementById("add-event-published").checked = true;
      // Show the other type field since "other" is default
      document.getElementById("add-other-type-container").style.display =
        "block";
      addEventModal.style.display = "flex";
    });
  }

  // --- Toggle "Other" type text field visibility for Add Event ---
  const addEventTypeSelect = document.getElementById("add-event-type");
  const addOtherTypeContainer = document.getElementById(
    "add-other-type-container"
  );
  if (addEventTypeSelect && addOtherTypeContainer) {
    addEventTypeSelect.addEventListener("change", () => {
      addOtherTypeContainer.style.display =
        addEventTypeSelect.value === "other" ? "block" : "none";
    });
  }

  // --- Toggle "Other" type text field visibility for Edit Event ---
  const editEventTypeSelect = document.getElementById("edit-event-type");
  const editOtherTypeContainer = document.getElementById(
    "edit-other-type-container"
  );
  if (editEventTypeSelect && editOtherTypeContainer) {
    editEventTypeSelect.addEventListener("change", () => {
      editOtherTypeContainer.style.display =
        editEventTypeSelect.value === "other" ? "block" : "none";
    });
  }

  // --- Add Event Modal Handlers ---
  if (addEventCancelBtn) {
    addEventCancelBtn.addEventListener("click", () => {
      addEventModal.style.display = "none";
      addEventForm.reset();
    });
  }

  if (addEventModal) {
    addEventModal.addEventListener("click", (e) => {
      if (e.target === addEventModal) {
        addEventModal.style.display = "none";
        addEventForm.reset();
      }
    });
  }

  if (addEventForm) {
    addEventForm.addEventListener("submit", (e) => {
      e.preventDefault();

      const title = document.getElementById("add-event-title").value;
      const startDate = document.getElementById("add-event-start").value;
      const endDate = document.getElementById("add-event-end").value;
      const eventType = document.getElementById("add-event-type").value;
      const otherType = document.getElementById("add-other-type").value;
      const isPublished = document.getElementById(
        "add-event-published"
      ).checked;

      if (!title || !startDate || !endDate) {
        showNotification("Please fill all required fields", "error");
        return;
      }

      // Validate custom type if "other" is selected
      if (eventType === "other" && !otherType.trim()) {
        showNotification("Please specify the event type", "error");
        return;
      }

      if (new Date(startDate) > new Date(endDate)) {
        showNotification("Start date cannot be after end date", "error");
        return;
      }

      const submitBtn = addEventForm.querySelector('button[type="submit"]');
      const originalText = submitBtn.textContent;
      submitBtn.disabled = true;
      submitBtn.textContent = "Adding...";

      fetch("../../server/api/events.php?action=add", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          title: title,
          start: startDate,
          end: endDate,
          type: eventType,
          custom_type: eventType === "other" ? otherType : null,
          published: isPublished ? 1 : 0,
        }),
      })
        .then((response) => {
          if (!response.ok) {
            throw new Error("HTTP error! status: " + response.status);
          }
          return response.text();
        })
        .then((text) => {
          try {
            return JSON.parse(text);
          } catch (e) {
            console.error("Response was not JSON:", text);
            throw new Error("Invalid server response");
          }
        })
        .then((data) => {
          if (data.success) {
            showNotification("Event added successfully!", "success");
            addEventModal.style.display = "none";
            addEventForm.reset();
            window.calendar.refetchEvents();
            loadAdminEvents();
          } else {
            showNotification(
              "Error: " + (data.error || "Could not add event"),
              "error"
            );
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          showNotification("Error adding event: " + error.message, "error");
        })
        .finally(() => {
          submitBtn.disabled = false;
          submitBtn.textContent = originalText;
        });
    });
  }

  if (manageEventsBtn && manageEventsModal) {
    manageEventsBtn.addEventListener("click", () => {
      manageEventsModal.style.display = "flex";
      loadAdminEvents();
    });

    closeManageEventsBtn.addEventListener("click", () => {
      manageEventsModal.style.display = "none";
    });

    manageEventsModal.addEventListener("click", (e) => {
      if (e.target === manageEventsModal) {
        manageEventsModal.style.display = "none";
      }
    });

    editEventCancelBtn.addEventListener("click", () => {
      editEventModal.style.display = "none";
      editEventForm.reset();
    });

    editEventModal.addEventListener("click", (e) => {
      if (e.target === editEventModal) {
        editEventModal.style.display = "none";
        editEventForm.reset();
      }
    });

    editEventForm.addEventListener("submit", (e) => {
      e.preventDefault();

      const eventId = document.getElementById("edit-event-id").value;
      const title = document.getElementById("edit-event-title").value;
      const startDate = document.getElementById("edit-event-start").value;
      const endDate = document.getElementById("edit-event-end").value;
      const eventType = document.getElementById("edit-event-type").value;
      const isPublished = document.getElementById(
        "edit-event-published"
      ).checked;

      if (!title || !startDate || !endDate) {
        showNotification("Please fill all required fields", "error");
        return;
      }

      if (new Date(startDate) > new Date(endDate)) {
        showNotification("Start date cannot be after end date", "error");
        return;
      }

      const submitBtn = editEventForm.querySelector('button[type="submit"]');
      const originalText = submitBtn.textContent;
      submitBtn.disabled = true;
      submitBtn.textContent = "Saving...";

      fetch("../../server/api/events.php?action=update", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          id: eventId,
          title: title,
          event_date: startDate,
          end_date: endDate,
          event_type: eventType,
          is_published: isPublished,
        }),
      })
        .then((response) => response.json())
        .then((data) => {
          submitBtn.disabled = false;
          submitBtn.textContent = originalText;

          if (data.success) {
            showNotification("Event updated successfully!", "success");
            editEventModal.style.display = "none";
            editEventForm.reset();
            loadAdminEvents();
            if (window.calendar) {
              window.calendar.refetchEvents();
            }
          } else {
            showNotification(
              "Error: " + (data.error || "Could not update event"),
              "error"
            );
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          submitBtn.disabled = false;
          submitBtn.textContent = originalText;
          showNotification("Error updating event", "error");
        });
    });
  }

  // --- Load and Display Admin Events ---
  function loadAdminEvents() {
    fetch("../../server/api/events.php?action=get_admin_events")
      .then((response) => {
        console.log(
          "API Response Status:",
          response.status,
          response.statusText
        );

        if (!response.ok) {
          const errorMessages = {
            401: "Unauthorized - Please log in again",
            403: "Forbidden - You do not have permission",
            404: "Not found",
            500: "Server error - Please try again later",
            503: "Database connection error",
          };
          const message =
            errorMessages[response.status] ||
            `HTTP ${response.status}: ${response.statusText}`;
          throw new Error(message);
        }
        return response.text();
      })
      .then((text) => {
        console.log("API Response:", text.substring(0, 500));

        if (!text || text.trim() === "") {
          throw new Error("Empty response from server");
        }
        try {
          const data = JSON.parse(text);
          if (data.success && data.events) {
            console.log("✓ Loaded", data.events.length, "events");
            displayEventsTable(data.events);
          } else if (data.error) {
            console.error("API Error:", data.error);
            showNotification("Error: " + data.error, "error");
          } else {
            console.error("Unexpected response:", data);
            showNotification("Unexpected response format", "error");
          }
        } catch (parseError) {
          console.error("JSON Parse Error:", parseError);
          console.error("Response text:", text);
          showNotification("Error loading events (invalid response)", "error");
        }
      })
      .catch((error) => {
        console.error("❌ Fetch Error:", error.message);
        showNotification("Error loading events: " + error.message, "error");
      });
  }

  // --- Display Events in Table ---
  function displayEventsTable(events) {
    const tableBody = document.getElementById("events-table-body");

    if (!tableBody) {
      console.error("Table body element not found");
      return;
    }

    if (events.length === 0) {
      tableBody.innerHTML =
        '<tr style="text-align: center;"><td colspan="6" style="padding: 2rem; color: #999;">No events found</td></tr>';
      return;
    }

    try {
      tableBody.innerHTML = events
        .map((event) => {
          const eventTypeColors = {
            holiday: { color: "#4ecdc4", label: "Holiday" },
            examination: { color: "#a29bfe", label: "Examination" },
            deadline: { color: "#ff6b6b", label: "Deadline" },
            celebration: { color: "#ffd93d", label: "Celebration" },
            meeting: { color: "#74b9ff", label: "Meeting" },
            other: { color: "#800000", label: "Other" },
          };

          const typeInfo =
            eventTypeColors[event.event_type] || eventTypeColors["other"];
          const isPublished =
            event.is_published == 1 || event.is_published === true;
          const statusBadge = isPublished
            ? '<span style="background-color: #e8f8f0; color: #27ae60; padding: 5px 12px; border-radius: 20px; font-weight: 600; font-size: 0.8rem;">Published</span>'
            : '<span style="background-color: #fff8e1; color: #f39c12; padding: 5px 12px; border-radius: 20px; font-weight: 600; font-size: 0.8rem;">Draft</span>';

          const title = (event.title || "Untitled")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;");
          const eventId = event.id || "";

          return `
                    <tr style="border-bottom: 1px solid #f0f0f0;">
                        <td style="padding: 1rem 1.5rem; color: #2d3436; font-weight: 500;">${title}</td>
                        <td style="padding: 1rem 1.5rem; color: #636e72;">${formatDate(
                          event.event_date
                        )}</td>
                        <td style="padding: 1rem 1.5rem; color: #636e72;">${formatDate(
                          event.end_date
                        )}</td>
                        <td style="padding: 1rem 1.5rem;">
                            <span style="background-color: ${
                              typeInfo.color
                            }20; color: ${
            typeInfo.color
          }; padding: 4px 10px; border-radius: 4px; font-size: 0.85rem; font-weight: 500;">${
            typeInfo.label
          }</span>
                        </td>
                        <td style="padding: 1rem 1.5rem; text-align: center;">${statusBadge}</td>
                        <td style="padding: 1rem 1.5rem; text-align: center;">
                            <button class="edit-event-btn" data-event-id="${eventId}" style="background: none; border: none; color: #800000; cursor: pointer; font-size: 1.2rem; margin-right: 0.5rem;" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="delete-event-btn" data-event-id="${eventId}" style="background: none; border: none; color: #f44336; cursor: pointer; font-size: 1.2rem;" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
        })
        .join("");

      // Add event listeners to edit and delete buttons
      document.querySelectorAll(".edit-event-btn").forEach((btn) => {
        btn.addEventListener("click", (e) => {
          e.preventDefault();
          const eventId = btn.getAttribute("data-event-id");
          openEditEventModal(events, eventId);
        });
      });

      document.querySelectorAll(".delete-event-btn").forEach((btn) => {
        btn.addEventListener("click", (e) => {
          e.preventDefault();
          const eventId = btn.getAttribute("data-event-id");
          const event = events.find((e) => e.id == eventId);
          if (
            event &&
            confirm(
              `Are you sure you want to delete the event "${event.title}"?`
            )
          ) {
            deleteEvent(eventId);
          }
        });
      });
    } catch (e) {
      console.error("Error displaying events table:", e);
      showNotification("Error displaying events", "error");
    }
  }

  // --- Format Date ---
  function formatDate(dateString) {
    if (!dateString) return "N/A";
    try {
      const options = { year: "numeric", month: "short", day: "numeric" };
      return new Date(dateString).toLocaleDateString("en-US", options);
    } catch (e) {
      console.error("Date format error:", dateString, e);
      return dateString || "N/A";
    }
  }

  // --- Open Edit Event Modal ---
  function openEditEventModal(events, eventId) {
    const event = events.find((e) => e.id == eventId);
    if (!event) return;

    document.getElementById("edit-event-id").value = event.id;
    document.getElementById("edit-event-title").value = event.title;
    document.getElementById("edit-event-start").value = event.start;
    document.getElementById("edit-event-end").value = event.end;
    document.getElementById("edit-event-type").value = event.event_type;
    document.getElementById("edit-event-published").checked =
      event.is_published == 1;

    document.getElementById("edit-event-modal").style.display = "flex";
  }

  // --- Delete Event ---
  function deleteEvent(eventId) {
    fetch("../../server/api/events.php?action=delete", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id: eventId }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          showNotification("Event deleted successfully!", "success");
          loadAdminEvents();
          if (window.calendar) {
            window.calendar.refetchEvents();
          }
        } else {
          showNotification(
            "Error: " + (data.error || "Could not delete event"),
            "error"
          );
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        showNotification("Error deleting event", "error");
      });
  }

  // --- Grading Period Modal Logic ---
  const gradingPeriodModal = document.getElementById("grading-period-modal");
  const addGradingPeriodBtn = document.getElementById("add-grading-period-btn");
  const gradingPeriodForm = document.getElementById("grading-period-form");
  const gpCancelBtn = document.getElementById("gp-cancel-btn");

  if (addGradingPeriodBtn && gradingPeriodModal) {
    addGradingPeriodBtn.addEventListener("click", () => {
      gradingPeriodModal.style.display = "flex";
    });

    gpCancelBtn.addEventListener("click", () => {
      gradingPeriodModal.style.display = "none";
      gradingPeriodForm.reset();
    });

    gradingPeriodModal.addEventListener("click", (e) => {
      if (e.target === gradingPeriodModal) {
        gradingPeriodModal.style.display = "none";
        gradingPeriodForm.reset();
      }
    });

    gradingPeriodForm.addEventListener("submit", (e) => {
      e.preventDefault();

      const quarter = document.getElementById("gp-quarter").value;
      const startDate = document.getElementById("gp-start-date").value;
      const endDate = document.getElementById("gp-end-date").value;

      if (!quarter || !startDate || !endDate) {
        showNotification("Please fill all fields", "error");
        return;
      }

      // Validate dates
      if (new Date(startDate) >= new Date(endDate)) {
        showNotification("Start date must be before end date", "error");
        return;
      }

      console.log("Adding grading period:", { quarter, startDate, endDate });

      // Show loading state
      const submitBtn = gradingPeriodForm.querySelector(
        'button[type="submit"]'
      );
      const originalText = submitBtn.textContent;
      submitBtn.disabled = true;
      submitBtn.textContent = "Adding...";

      fetch("../../server/api/grading_periods.php?action=add_grading_period", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          quarter: parseInt(quarter),
          start_date: startDate,
          end_date: endDate,
        }),
      })
        .then((response) => {
          console.log("Response status:", response.status);
          return response.text().then((text) => {
            try {
              return { status: response.status, data: JSON.parse(text) };
            } catch (e) {
              return {
                status: response.status,
                data: { error: "Invalid JSON response: " + text },
              };
            }
          });
        })
        .then(({ status, data }) => {
          console.log("API Response:", { status, data });

          // Reset button state
          submitBtn.disabled = false;
          submitBtn.textContent = originalText;

          if (status === 200 && data.success) {
            showNotification(
              "✓ Grading period added successfully!",
              "success",
              4000
            );
            gradingPeriodModal.style.display = "none";
            gradingPeriodForm.reset();
            // Reload calendar to show new grading period
            if (window.calendar) {
              window.calendar.refetchEvents();
            }
          } else if (status === 403) {
            showNotification(
              "Admin access required to add grading periods",
              "error",
              4000
            );
          } else if (status === 401) {
            showNotification(
              "Session expired. Please log in again.",
              "error",
              4000
            );
          } else {
            showNotification(
              "Error: " + (data.error || "Could not add grading period"),
              "error",
              4000
            );
          }
        })
        .catch((error) => {
          console.error("Network Error:", error);
          submitBtn.disabled = false;
          submitBtn.textContent = originalText;
          showNotification("Network error: " + error.message, "error", 4000);
        });
    });
  }
});
