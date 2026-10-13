
<?php
$e = static fn($value) =>
    htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>

<div class="container-fluid py-4">

    <div class="d-flex align-items-center gap-3 mb-4">

        <a
            href="<?= BASE_URL ?>/payroll"
            class="btn btn-outline-secondary">
            <i data-lucide="arrow-left"></i>
        </a>

        <div>
            <h2 class="fw-bold mb-1">
                Create Payroll Period
            </h2>

            <p class="text-muted mb-0">
                Define the payroll coverage and pay date.
            </p>
        </div>

    </div>

    <?php if (!empty($error)): ?>

        <div class="alert alert-danger">
            <i data-lucide="circle-alert" class="me-2"></i>
            <?= $e($error) ?>
        </div>

    <?php endif; ?>

    <div class="row justify-content-center">

        <div class="col-lg-8 col-xl-7">

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0">
                        Payroll Period Details
                    </h5>
                </div>

                <div class="card-body p-4">

                    <form
                        method="POST"
                        action="<?= BASE_URL ?>/payroll/create"
                        id="payrollForm">

                        <div class="mb-3">

                            <label class="form-label fw-semibold">
                                Period Name
                            </label>

                            <input
                                type="text"
                                name="period_name"
                                class="form-control"
                                placeholder="Example: September 1–15, 2026"
                                maxlength="100"
                                required>

                        </div>

                        <div class="row">

                            <div class="col-md-6 mb-3">

                                <label class="form-label fw-semibold">
                                    Start Date
                                </label>

                                <input
                                    type="date"
                                    name="start_date"
                                    id="start_date"
                                    class="form-control"
                                    required>

                            </div>

                            <div class="col-md-6 mb-3">

                                <label class="form-label fw-semibold">
                                    End Date
                                </label>

                                <input
                                    type="date"
                                    name="end_date"
                                    id="end_date"
                                    class="form-control"
                                    required>

                            </div>

                        </div>

                        <div class="mb-4">

                            <label class="form-label fw-semibold">
                                Pay Date
                            </label>

                            <input
                                type="date"
                                name="pay_date"
                                id="pay_date"
                                class="form-control">

                            <div class="form-text">
                                Optional. This is the date employees are
                                scheduled to receive their payroll.
                            </div>

                        </div>

                        <div class="alert alert-info">

                            <div class="d-flex gap-2">

                                <i data-lucide="info"></i>

                                <div>

                                    <strong>Payroll workflow</strong>

                                    <div class="small mt-1">
                                        Draft → Processing → Review →
                                        Approved → Paid → Locked
                                    </div>

                                </div>

                            </div>

                        </div>

                        <div class="d-flex justify-content-end gap-2">

                            <a
                                href="<?= BASE_URL ?>/payroll"
                                class="btn btn-light border">
                                Cancel
                            </a>

                            <button
                                type="submit"
                                class="btn btn-primary">
                                <i data-lucide="save" class="me-1"></i>
                                Create Payroll Period
                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    const start = document.getElementById('start_date');
    const end = document.getElementById('end_date');
    const pay = document.getElementById('pay_date');

    function validateDates() {

        if (
            start.value &&
            end.value &&
            start.value > end.value
        ) {
            end.setCustomValidity(
                'End date must not be earlier than start date.'
            );
        } else {
            end.setCustomValidity('');
        }

        if (
            end.value &&
            pay.value &&
            pay.value < end.value
        ) {
            pay.setCustomValidity(
                'Pay date should normally be on or after the payroll end date.'
            );
        } else {
            pay.setCustomValidity('');
        }
    }

    start.addEventListener('change', validateDates);
    end.addEventListener('change', validateDates);
    pay.addEventListener('change', validateDates);

});
</script>

