@extends('layouts.master')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="page-title mb-0 font-size-18">Tax Rate</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">CRM</a></li>
                            <li class="breadcrumb-item active">Tax Rate</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" style="font-size: 12px; table-layout: fixed; width: 100%;">
                                <thead>
                                    <tr>
                                        <th>Status</th>
                                        <th>PTKP</th>
                                        <th>(%) Pajak</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($taxRates as $taxRate)
                                        <tr>
                                            <td>{{ $taxRate->marriage_status }}</td>
                                            <td>{{ number_format($taxRate->ptkp, 0, ',', '.') }}</td>
                                            <td>{{ number_format($taxRate->tax_percentage, 2, ',', '.') }}%</td>
                                            <td>
                                                <!-- Edit Button -->
                                                <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal{{ $taxRate->id }}">
                                                    Edit
                                                </button>
                                                <!-- Delete Button -->
                                                <form action="{{ route('taxrates.destroy', $taxRate->id) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this tax rate?')">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
@foreach ($taxRates as $taxRate)
    <div class="modal fade" id="editModal{{ $taxRate->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $taxRate->id }}" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('taxrates.update', $taxRate->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel{{ $taxRate->id }}">Edit Tax Rate</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="marriage_status" class="col-form-label">Marriage Status:</label>
                            <input type="text" class="form-control" id="marriage_status" name="marriage_status" value="{{ $taxRate->marriage_status }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="ptkp" class="col-form-label">PTKP:</label>
                            <input type="number" class="form-control" id="ptkp" name="ptkp" value="{{ $taxRate->ptkp }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="tax_percentage" class="col-form-label">Tax Percentage:</label>
                            <input type="number" class="form-control" id="tax_percentage" name="tax_percentage" value="{{ $taxRate->tax_percentage }}" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

@endsection
