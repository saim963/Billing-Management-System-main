$(document).ready(function() {
    $("#addRow").click(function() {
        if ($(".entry-row").length >= 10) {
            alert("Maximum 10 entries are allowed!");
            return;
        }
        var newRow = $(".entry-row:first").clone();
        newRow.find("input, select").val("");
        newRow.find(".rate-cell").text("0");
        newRow.attr('data-index', $(".entry-row").length); // Unique index for new rows
        $("#remunerationTable tbody").append(newRow);
        setupRateFetch(newRow); // Setup AJAX for new row
    });

    // Use event delegation for remove-row buttons
    $(document).on("click", ".remove-row", function() {
        if ($(".entry-row").length > 1) {
            $(this).closest("tr").remove();
        } else {
            // Reset the form when removing the last row
            $(".entry-row").find("input, select").val("");
            $(".entry-row").find(".rate-cell").text("0");
        }
    });

    // Handle form submission via AJAX
    $('#remunerationForm').submit(function(e) {
        e.preventDefault();
        
        let valid = true;
        $(".entry-row").each(function() {
            let dateFrom = $(this).find("[name='date_from[]']").val();
            let dateTo = $(this).find("[name='date_to[]']").val();
            let candidates = $(this).find("[name='candidates[]']").val();

            if (new Date(dateTo) < new Date(dateFrom)) {
                alert("End date must be after start date!");
                valid = false;
                return false;
            }
            if (!$.isNumeric(candidates) || parseInt(candidates) < 1) {
                alert("Candidates must be a positive number!");
                valid = false;
                return false;
            }
        });

        if (!valid) return;

        $.ajax({
            url: 'welcome.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                // Update workplace dropdown
                $.get('get_workplaces.php', function(data) {
                    if (data.success) {
                        let select = $('#workplaceSelect');
                        select.empty();
                        select.append('<option value="">Select Workplace</option>');
                        data.workplaces.forEach(function(workplace) {
                            select.append($('<option></option>')
                                .attr('value', workplace)
                                .text(workplace));
                        });
                    }
                }, 'json');

                // Reset the form
                $(".entry-row").find("input, select").val("");
                $(".entry-row").find(".rate-cell").text("0");
                
                // Reload the page to show updated data
                location.reload();
            },
            error: function() {
                alert('An error occurred while submitting the form. Please try again.');
            }
        });
    });

    // Setup AJAX for existing rows
    $(".entry-row").each(function() {
        setupRateFetch($(this));
    });

    function setupRateFetch(row) {
        var courseSelect = row.find(".course-select");
        var jobSelect = row.find(".job-select");
        var rateCell = row.find(".rate-cell");

        function updateRate() {
            var course = courseSelect.val();
            var job = jobSelect.val();

            if (course && job) {
                $.ajax({
                    url: 'get_rate.php',
                    method: 'POST',
                    data: {
                        course: course,
                        job: job
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            rateCell.text(response.rate);
                        } else {
                            rateCell.text("0");
                            alert("Error fetching rate: " + response.message);
                        }
                    },
                    error: function() {
                        rateCell.text("0");
                        alert("Failed to fetch rate. Please try again.");
                    }
                });
            } else {
                rateCell.text("0");
            }
        }

        courseSelect.on('change', updateRate);
        jobSelect.on('change', updateRate);

        // Initial call if values are already selected
        updateRate();
    }
});

function editRow(rowData) {
    // Implement AJAX call to load data back into form
    alert("Edit functionality not fully implemented. Data: " + JSON.stringify(rowData));
}

function deleteRow(employ_id, workplace, course, date_from) {
    if (confirm("Are you sure you want to delete this entry?")) {
        $.post("delete_waiting.php", {
            employ_id: employ_id,
            workplace: workplace,
            course: course,
            date_from: date_from
        }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert("Delete failed: " + response.message);
            }
        }, "json");
    }
}

// for remark modal
function showRemarkModal(remark) {
    const remarkContent = document.getElementById('remarkContent');
    remarkContent.textContent = remark || 'No remark provided.';
    $('#remarkModal').modal('show');
}