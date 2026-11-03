
    <div class="container-fluid">
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="card-title mb-0">
                <i class="mdi mdi-account-cancel me-1"></i> Deactivation Records
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="deactivation-table">
                    <thead>
                        <tr>
                             <th>Date</th>
                            <th>Action</th>
                            <th>Action By</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shop->deactivationHistory as $history)
                            <tr class="{{ $history->action === 'deactivated'}}">
                                <td>{{ $history->created_at->format('d M Y h:i A') }}</td>
                                <td>
                                    <span class="badge bg-{{ $history->action === 'deactivated' ? 'danger' : 'success' }}">
                                        {{ ucfirst($history->action) }}
                                    </span>
                                </td>
                                <td>{{ $history->actionBy->name }}</td>
                                <td>{{ $history->reason }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">No deactivation history found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
