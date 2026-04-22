<div class="modal-reminder-container" data-reminder-id="{{ $reminder->id }}" style="padding: 0; display: flex; position: relative; flex-direction: column; height: 100%; max-height: calc(90vh - 60px);">
    
    <!-- Scrollable Content Area -->
    <div style="flex: 1; overflow-y: auto; padding: 0;">
        
        <!-- Reminder Header -->
        <div style="padding: 16px 16px 12px 16px; margin: 0; display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1px solid #e4e6eb;">
            <div style="display: flex; gap: 12px;">
                <div style="width: 48px; height: 48px; border-radius: 50%; overflow: hidden; flex-shrink: 0; background: linear-gradient(135deg, #28a745, #20c997); display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-pills" style="color: white; font-size: 24px;"></i>
                </div>
                <div>
                    <h6 style="font-size: 15px; font-weight: 600; margin: 0; padding: 0; color: #1a1a1a;">
                        Medicine Reminder
                    </h6>
                    <div style="display: flex; align-items: center; gap: 12px; font-size: 12px; color: #65676b; margin: 0; padding: 0;">
                        <span><i class="far fa-clock me-1"></i>{{ $reminder->reminder_at->format('M d, Y h:i A') }}</span>
                        <span class="badge {{ $reminder->status === 'pending' ? 'bg-warning' : ($reminder->status === 'taken' ? 'bg-success' : 'bg-danger') }}">
                            {{ ucfirst($reminder->status) }}
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Close Button -->
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="background: none; border: none; font-size: 20px; cursor: pointer; opacity: 0.6;"></button>
        </div>

        <!-- Medicine Details -->
        <div style="padding: 16px;">
            <div style="background: #f8f9fa; border-radius: 12px; padding: 16px; margin-bottom: 16px;">
                <h5 style="margin: 0 0 12px 0; color: #28a745; font-weight: 700;">
                    <i class="fas fa-capsules me-2"></i>{{ $reminder->schedule->medicine->medicine_name }}
                </h5>
                
                <div style="display: grid; gap: 12px;">
                    @if($reminder->schedule->medicine->type)
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <i class="fas fa-tag" style="width: 20px; color: #6c757d;"></i>
                            <span><strong>Type:</strong> {{ ucfirst($reminder->schedule->medicine->type) }}</span>
                        </div>
                    @endif
                    
                    @if($reminder->schedule->medicine->value_per_dose)
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <i class="fas fa-weight" style="width: 20px; color: #6c757d;"></i>
                            <span><strong>Dosage:</strong> {{ $reminder->schedule->medicine->value_per_dose }} {{ $reminder->schedule->medicine->unit }}</span>
                        </div>
                    @endif
                    
                    @if($reminder->schedule->medicine->rule)
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <i class="fas fa-clock" style="width: 20px; color: #6c757d;"></i>
                            <span><strong>When to take:</strong> {{ $reminder->schedule->medicine->ruleLabel }}</span>
                        </div>
                    @endif
                    
                    @if($reminder->schedule->medicine->dose_limit)
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <i class="fas fa-ban" style="width: 20px; color: #6c757d;"></i>
                            <span><strong>Daily limit:</strong> {{ $reminder->schedule->medicine->dose_limit }} doses</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Schedule Information -->
            <div style="background: #e7f3ff; border-radius: 12px; padding: 16px; margin-bottom: 16px;">
                <h6 style="margin: 0 0 10px 0; color: #1877f2; font-weight: 700;">
                    <i class="fas fa-calendar-alt me-2"></i>Schedule Information
                </h6>
                <div style="display: grid; gap: 8px;">
                    <div><strong>Start Date:</strong> {{ \Carbon\Carbon::parse($reminder->schedule->start_date)->format('M d, Y') }}</div>
                    @if($reminder->schedule->end_date)
                        <div><strong>End Date:</strong> {{ \Carbon\Carbon::parse($reminder->schedule->end_date)->format('M d, Y') }}</div>
                    @endif
                    @if($reminder->schedule->frequency_per_day)
                        <div><strong>Frequency:</strong> {{ $reminder->schedule->frequency_per_day }} time(s) per day</div>
                    @endif
                </div>
            </div>

            <!-- Notes if any -->
            @if($reminder->schedule->medicine->notes)
                <div style="background: #fff3cd; border-radius: 12px; padding: 16px;">
                    <h6 style="margin: 0 0 8px 0; color: #856404; font-weight: 700;">
                        <i class="fas fa-sticky-note me-2"></i>Notes
                    </h6>
                    <p style="margin: 0; color: #856404;">{{ $reminder->schedule->medicine->notes }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Sticky Action Buttons at Bottom -->
    <div style="border-top: 1px solid #e4e6eb; padding: 12px 16px; background: white; flex-shrink: 0; display: flex; gap: 8px;">
        @if($reminder->status === 'pending')
            <button onclick="markReminderTakenFromModal({{ $reminder->id }})" 
                    class="btn btn-success w-100" 
                    style="border-radius: 8px; padding: 10px; font-weight: 600;">
                <i class="fas fa-check me-2"></i>Taken
            </button>
        @elseif($reminder->status === 'taken')
            <div class="alert alert-success w-100 text-center mb-0" style="border-radius: 8px;">
                <i class="fas fa-check-circle me-2"></i>Taken at {{ $reminder->taken_at ? $reminder->taken_at->format('h:i A') : 'Unknown time' }}
            </div>
        @else
            <div class="alert alert-danger w-100 text-center mb-0" style="border-radius: 8px;">
                <i class="fas fa-times-circle me-2"></i>Not Taken
            </div>
        @endif
    </div>
</div>

<style>
.modal-reminder-container {
    background: white;
    color: #1a1a1a;
    font-family: inherit;
}

.modal-reminder-container * {
    box-sizing: border-box;
}

.modal-reminder-container button {
    cursor: pointer;
    transition: all 0.2s ease;
}

.modal-reminder-container button:hover {
    transform: translateY(-1px);
}

.btn-close:hover {
    opacity: 1 !important;
    background: rgba(0,0,0,0.05);
    border-radius: 50%;
}
</style>