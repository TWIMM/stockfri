@extends('layouts.app_layout')

@section('title', 'Gestion des Coéquipiers')

@section('content')

    @include('Modals.remboursement')
    @include('Modals.commande_owned')

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h5>Créentié(e)s</h5>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-view">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Dette actuelle</th>
                                    <th>Limite de crédit</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($clients as $coequipier)
                                    <tr>
                                        <td>{{ $coequipier->name }}</td>
                                        <td>{{ number_format($coequipier->current_debt) }} FCFA</td>
                                        <td>{{ number_format($coequipier->limit_credit_for_this_user) }} FCFA</td>
                                        <td>

                                            <button type="button" class="btn bg-blue edit-btn" data-id="{{ $coequipier->id }}"
                                                data-name="{{ $coequipier->name }}" data-email="{{ $coequipier->email }}"
                                                data-tel="{{ $coequipier->tel }}" data-bs-toggle="modal"
                                                data-bs-target="#CommandeImpayes">
                                                <i class="ti ti-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">Aucun coéquipier trouvé.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="d-flex justify-content-center">
                        {{ $clients->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        // JavaScript to handle the client debts modal
        document.addEventListener('DOMContentLoaded', function() {
            // Event listener for the CommandeImpayes modal buttons
            document.querySelectorAll('.edit-btn').forEach(button => {
                button.addEventListener('click', function() {
                    // Check if this button is for the CommandeImpayes modal
                    if (this.getAttribute('data-bs-target') === '#CommandeImpayes') {
                        const clientId = this.getAttribute('data-id');
                        const clientName = this.getAttribute('data-name');

                        // Update modal title with client name
                        document.getElementById('modalVenteLabel').textContent =
                            `Commandes impayées - ${clientName}`;

                        // Store client ID for later use
                        document.getElementById('put_id_in_there').value = clientId;

                        // Show loading indicator
                        const tableBody = document.querySelector('#CommandeImpayes table tbody');
                        tableBody.innerHTML =
                            '<tr><td colspan="6" class="text-center">Chargement des données...</td></tr>';

                        // Fetch client debts data
                        fetch(`/clients/${clientId}/debts`)
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error('Erreur lors du chargement des données');
                                }
                                return response.json();
                            })
                            .then(debts => {
                                // Clear the loading message
                                tableBody.innerHTML = '';

                                if (debts.length === 0) {
                                    // No debts found
                                    tableBody.innerHTML =
                                        '<tr><td colspan="6" class="text-center">Aucune commande impayée trouvée.</td></tr>';
                                    return;
                                }

                                // Populate the table with debts
                                debts.forEach(debt => {
                                    const dueDate = new Date(debt.due_date);
                                    const now = new Date();
                                    const isLate = now > dueDate;

                                    const row = document.createElement('tr');
                                    row.innerHTML = `
                                <td>${debt.commande_id}</td>
                                <td>${Number(debt.amount).toLocaleString()} FCFA</td>
                                <td>${formatDate(debt.due_date)}</td>
                                <td>
                                    ${isLate 
                                        ? '<span class="badge bg-danger">En retard</span>' 
                                        : '<span class="badge bg-success">À temps</span>'}
                                </td>
                                <td>

                                    <button  type="button" class="btn btn-sm btn-secondary edit-btn"
                                                data-id="{{ $coequipier->id }}" data-name="{{ $coequipier->name }}"
                                                data-email="{{ $coequipier->email }}" data-tel="{{ $coequipier->tel }}"
                                                data-bs-toggle="modal" data-bs-target="#remboursementModal">
                                                <i class="ti ti-receipt"></i>
                                            </button>
                                    <a href="/commandes/${debt.commande_id}" class="btn btn-info btn-sm">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                   
                                </td>
                            `;
                                    tableBody.appendChild(row);
                                });

                                // Add event listeners to mark-paid buttons
                                addMarkPaidEventListeners();
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                tableBody.innerHTML =
                                    `<tr><td colspan="6" class="text-center text-danger">${error.message}</td></tr>`;
                            });
                    }
                });
            });

            // Function to add event listeners to mark-paid buttons
            function addMarkPaidEventListeners() {
                document.querySelectorAll('.mark-paid-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        const debtId = this.getAttribute('data-debt-id');
                        if (confirm('Êtes-vous sûr de vouloir marquer cette dette comme payée?')) {
                            markDebtAsPaid(debtId);
                        }
                    });
                });
            }

            // Function to mark a debt as paid
            function markDebtAsPaid(debtId) {
                // Get CSRF token from meta tag
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch(`/client-debts/${debtId}/pay`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Erreur lors du traitement de la demande');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Get client ID from hidden input
                            const clientId = document.getElementById('put_id_in_there').value;

                            // Reload client debts
                            fetch(`/clients/${clientId}/debts`)
                                .then(response => response.json())
                                .then(debts => {
                                    const tableBody = document.querySelector(
                                        '#CommandeImpayes table tbody');
                                    tableBody.innerHTML = '';

                                    if (debts.length === 0) {
                                        tableBody.innerHTML =
                                            '<tr><td colspan="6" class="text-center">Aucune commande impayée trouvée.</td></tr>';
                                        return;
                                    }

                                    // Re-populate the table with updated debts
                                    debts.forEach(debt => {
                                        const dueDate = new Date(debt.due_date);
                                        const now = new Date();
                                        const isLate = now > dueDate;

                                        const row = document.createElement('tr');
                                        row.innerHTML = `
                                <td>${debt.commande_id}</td>
                                <td>${Number(debt.amount).toLocaleString()} FCFA</td>
                                <td>${debt.payment_method}</td>
                                <td>${formatDate(debt.due_date)}</td>
                                <td>
                                    ${isLate 
                                        ? '<span class="badge bg-danger">En retard</span>' 
                                        : '<span class="badge bg-success">À temps</span>'}
                                </td>
                                <td>
                                    <a href="/commandes/${debt.commande_id}" class="btn btn-info btn-sm">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-success btn-sm mark-paid-btn" data-debt-id="${debt.id}">
                                        <i class="ti ti-check"></i>
                                    </button>
                                </td>
                            `;
                                        tableBody.appendChild(row);
                                    });

                                    // Re-add event listeners
                                    addMarkPaidEventListeners();
                                });

                            // Show success message (customize based on your notification system)
                            showNotification('Succès', 'Dette marquée comme payée avec succès', 'success');
                        } else {
                            showNotification('Erreur', data.message || 'Une erreur est survenue', 'danger');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('Erreur', error.message, 'danger');
                    });
            }

            // Helper function to format dates
            function formatDate(dateString) {
                const date = new Date(dateString);
                const day = date.getDate().toString().padStart(2, '0');
                const monthNames = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov',
                    'Déc'
                ];
                const month = monthNames[date.getMonth()];
                const year = date.getFullYear();
                return `${day} ${month} ${year}`;
            }

            // Function to show notifications (adapt to your notification system)
            function showNotification(title, message, type) {
                // Check if you have a notification library like Toastify or SweetAlert
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: title,
                        text: message,
                        icon: type === 'success' ? 'success' : 'error',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                } else if (typeof toastr !== 'undefined') {
                    toastr[type === 'success' ? 'success' : 'error'](message, title);
                } else {
                    // Fallback to alert
                    alert(`${title}: ${message}`);
                }
            }
        });
        // Listen for click events on "Edit" buttons
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function() {
                // Get the data-id from the clicked button
                const clientId = this.getAttribute('data-id');
                document.getElementById('put_id_in_there').value = clientId;

                // Use AJAX to get the client data based on the ID
                fetch(`/clients/${clientId}`)
                    .then(response => response.json())
                    .then(data => {
                        // Populate the modal fields with the retrieved data
                        document.getElementById('edit-client-id').value = data.id;
                        document.getElementById('edit-name').value = data.name;
                        document.getElementById('edit-email').value = data.email;
                        document.getElementById('edit-tel').value = data.tel;
                        document.getElementById('edit-address').value = data.address;

                        // Set the form action to the correct route for the update
                        document.getElementById('editForm').action = `/clients/update/${data.id}`;
                    })
                    .catch(error => console.error('Error fetching client data:', error));
            });
        });
    </script>

@endsection
