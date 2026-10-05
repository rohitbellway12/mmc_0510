{{-- Reject / Ignore Booking Reason Modal --}}
<div class="modal fade" id="rejectBookingModal" tabindex="-1" aria-labelledby="rejectBookingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header border-0 pb-0 pt-3 px-3">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="rejectBookingForm" method="POST" action="">
                @csrf
                <div class="modal-body pt-0 px-4">
                    <div class="text-center mb-3">
                        <div class="d-inline-flex justify-content-center align-items-center rounded-circle mb-2" 
                             style="width: 56px; height: 56px; background-color: rgba(239, 68, 68, 0.12); color: #ef4444;">
                            <span class="material-icons" style="font-size: 32px;">cancel</span>
                        </div>
                        <h4 class="modal-title fs-18 fw-bold text-dark" id="rejectBookingModalLabel">
                            {{ translate('Reject_Booking_Request') }}
                        </h4>
                        <div id="rejectBookingReadableIdWrapper" style="display: none;" class="mt-1">
                            <span class="badge bg-danger-subtle text-danger px-2 py-1 fs-12 fw-semibold">
                                {{ translate('Booking') }}: <span id="rejectBookingReadableId"></span>
                            </span>
                        </div>
                        <p class="text-muted fz-13 mt-2 mb-0">
                            {{ translate('Please state the reason for rejecting/ignoring this booking.') }}
                        </p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold fz-13 mb-2 text-secondary">
                            {{ translate('Select_Quick_Reason') }}
                        </label>
                        <div class="d-flex flex-wrap gap-2" id="quickReasonChips">
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill fz-12 py-1 px-3 quick-reason-btn" 
                                    data-reason="{{ translate('Currently unavailable / Fully booked') }}">
                                {{ translate('Currently unavailable') }}
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill fz-12 py-1 px-3 quick-reason-btn" 
                                    data-reason="{{ translate('Location is too far / Out of service area') }}">
                                {{ translate('Out of service area') }}
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill fz-12 py-1 px-3 quick-reason-btn" 
                                    data-reason="{{ translate('Staff / Equipment not available') }}">
                                {{ translate('Staff unavailable') }}
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill fz-12 py-1 px-3 quick-reason-btn" 
                                    data-reason="{{ translate('Schedule or timing conflict') }}">
                                {{ translate('Timing conflict') }}
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill fz-12 py-1 px-3 quick-reason-btn" 
                                    data-reason="{{ translate('Pricing / Cost mismatch') }}">
                                {{ translate('Price mismatch') }}
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="rejectReasonTextarea" class="form-label fw-semibold fz-13 mb-1 text-secondary">
                            {{ translate('Reason_for_Rejection') }} <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="rejectReasonTextarea" name="reason" rows="3" 
                                  placeholder="{{ translate('Enter reason for rejection here...') }}" 
                                  style="border-radius: 8px; resize: vertical;" required></textarea>
                    </div>

                    <div class="alert alert-warning py-2 px-3 mb-0 d-flex align-items-center gap-2" style="border-radius: 8px; font-size: 12px;">
                        <span class="material-icons fs-16 text-warning">info</span>
                        <span>{{ translate('Once ignored, this booking request will be removed from your pending list.') }}</span>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0 px-4 pb-4 gap-2 d-flex">
                    <button type="button" class="btn btn-secondary flex-grow-1" data-bs-dismiss="modal">
                        {{ translate('Cancel') }}
                    </button>
                    <button type="submit" class="btn btn-danger flex-grow-1" id="confirmRejectSubmitBtn">
                        {{ translate('Reject_Booking') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        if (window.bookingIgnoreModalInitialized) return;
        window.bookingIgnoreModalInitialized = true;

        $(document).on('click', '.btn-reject-booking', function(e) {
            e.preventDefault();
            let actionUrl = $(this).data('action-url');
            let readableId = $(this).data('readable-id');

            $('#rejectBookingForm').attr('action', actionUrl);
            $('#rejectReasonTextarea').val('');
            $('.quick-reason-btn').removeClass('active bg-danger text-white border-danger');

            if (readableId) {
                $('#rejectBookingReadableId').text('#' + readableId);
                $('#rejectBookingReadableIdWrapper').show();
            } else {
                $('#rejectBookingReadableIdWrapper').hide();
            }

            let modalEl = document.getElementById('rejectBookingModal');
            let modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            modal.show();
        });

        $(document).on('click', '.quick-reason-btn', function() {
            let reason = $(this).data('reason');
            $('.quick-reason-btn').removeClass('active bg-danger text-white border-danger');
            $(this).addClass('active bg-danger text-white border-danger');
            $('#rejectReasonTextarea').val(reason).focus();
        });

        $('#rejectBookingForm').on('submit', function(e) {
            let reason = $('#rejectReasonTextarea').val().trim();
            if (!reason) {
                e.preventDefault();
                if (typeof toastr !== 'undefined') {
                    toastr.error("{{ translate('Please enter a rejection reason') }}");
                } else {
                    alert("{{ translate('Please enter a rejection reason') }}");
                }
                $('#rejectReasonTextarea').focus();
                return false;
            }
            $('#confirmRejectSubmitBtn').attr('disabled', true).html('{{ translate('Rejecting...') }}');
        });
    });
</script>
