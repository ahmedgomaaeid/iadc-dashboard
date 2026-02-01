import { Calendar } from "@fullcalendar/core";
import dayGridPlugin from "@fullcalendar/daygrid";
import timeGridPlugin from "@fullcalendar/timegrid";
import interactionPlugin from "@fullcalendar/interaction";

document.addEventListener("DOMContentLoaded", function () {
    const calendarEl = document.getElementById("calendar");
    if (!calendarEl) return;

    const calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
        initialView: "dayGridMonth",
        headerToolbar: {
            left: "prev,next today",
            center: "title",
            right: "dayGridMonth,timeGridWeek,timeGridDay",
        },
        editable: true,
        selectable: true,
        events: window.calendarEvents || [], // Pass events from Blade
        select: function (info) {
            // Open modal to add event
            document.getElementById("start_time").value = info.startStr;
            document.getElementById("end_time").value = info.endStr;
            // If strictly day grid, end date is exclusive, might need adjustment for UI

            // Show modal (assuming Bootstrap 5)
            const modal = new bootstrap.Modal(
                document.getElementById("eventModal"),
            );
            modal.show();
        },
        eventClick: function (info) {
            if (info.event.url) {
                info.jsEvent.preventDefault(); // don't let the browser navigate
                window.open(info.event.url);
            }
        },
    });

    calendar.render();

    // Handle form submission
    const saveBtn = document.getElementById("saveEventBtn");
    if (saveBtn) {
        saveBtn.addEventListener("click", function () {
            const title = document.getElementById("eventTitle").value;
            const description =
                document.getElementById("eventDescription").value;
            const start = document.getElementById("start_time").value;
            const end = document.getElementById("end_time").value;

            if (!title) {
                alert("Please enter a title");
                return;
            }

            // Simple validation or loading state
            this.disabled = true;
            this.innerText = "Saving...";

            fetch("/highboard/sessions", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                },
                body: JSON.stringify({
                    title: title,
                    description: description,
                    start_time: start,
                    end_time: end,
                    // valid committee logic might be needed here, or handled by backend default
                }),
            })
                .then((response) => response.json())
                .then((data) => {
                    this.disabled = false;
                    this.innerText = "Save";

                    if (data.error) {
                        alert("Error: " + data.error);
                    } else {
                        // Add event to calendar
                        calendar.addEvent({
                            title: data.event.title,
                            start: data.event.start_time,
                            end: data.event.end_time,
                            url: data.event.session_url,
                        });

                        // Close modal
                        const modalEl = document.getElementById("eventModal");
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        modal.hide();

                        // Clear form
                        document.getElementById("eventForm").reset();
                        alert("Event created successfully!");
                    }
                })
                .catch((error) => {
                    this.disabled = false;
                    this.innerText = "Save";
                    console.error("Error:", error);
                    alert("An error occurred while saving.");
                });
        });
    }
});
