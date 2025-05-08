<?php 
// $pageTitle zaten layout tarafından ayarlanıyor olacak.
// $tables AdminTableController tarafından gönderiliyor.
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Masa Yönetimi</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/admin">Admin Panel</a></li>
                    <li class="breadcrumb-item active">Masalar</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-plus-circle"></i> Yeni Masa Ekle</h3>
                    </div>
                    <form id="add-table-form" action="/admin/tables/store" method="POST">
                        <div class="card-body">
                            <div id="table-message" class="message" style="display: none;"></div> <!-- For AJAX messages -->
                            <div class="form-group">
                                <label for="table_name">Masa Adı / Numarası:</label>
                                <input type="text" id="table_name" name="table_name" class="form-control" placeholder="Örn: Masa 1, Bahçe 3, VIP" required>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Kaydet</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-list-ul"></i> Mevcut Masalar</h3>
                    </div>
                    <div class="card-body p-0">
                        <div id="table-list-message" class="message" style="display: none; margin: 15px;"></div>
                        <?php if (empty($tables)): ?>
                            <p class="text-center p-3">Henüz hiç masa eklenmemiş.</p>
                        <?php else: ?>
                            <table class="table table-striped projects">
                                <thead>
                                    <tr>
                                        <th style="width: 10%">#ID</th>
                                        <th style="width: 40%">Masa Adı</th>
                                        <th style="width: 20%">Durum</th>
                                        <th style="width: 30%">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody id="table-list-container">
                                    <?php foreach ($tables as $table): ?>
                                        <tr data-table-id="<?php echo $table->id; ?>">
                                            <td><?php echo $table->id; ?></td>
                                            <td><?php echo htmlspecialchars($table->name); ?></td>
                                            <td>
                                                <?php if ($table->is_active): ?>
                                                    <span class="badge badge-success">Aktif</span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger">Pasif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="project-actions text-right">
                                                <button class="btn btn-sm btn-secondary table-toggle-status-btn"
                                                        data-table-id="<?php echo $table->id; ?>"
                                                        data-current-status="<?php echo $table->is_active ? '1' : '0'; ?>">
                                                    <i class="fas fa-<?php echo $table->is_active ? 'toggle-off' : 'toggle-on'; ?>"></i> 
                                                    <?php echo $table->is_active ? 'Pasif Yap' : 'Aktif Yap'; ?>
                                                </button>
                                                <!-- Düzenle butonu daha sonra eklenebilir -->
                                                <!-- <a class="btn btn-info btn-sm" href="/admin/tables/edit?id=<?php echo $table->id; ?>">
                                                    <i class="fas fa-pencil-alt"></i> Düzenle
                                                </a> -->
                                                <button class="btn btn-danger btn-sm table-delete-btn"
                                                        data-table-id="<?php echo $table->id; ?>"
                                                        data-table-name="<?php echo htmlspecialchars($table->name); ?>">
                                                    <i class="fas fa-trash"></i> Sil
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tableListContainer = document.getElementById('table-list-container');
    const tableListMessage = document.getElementById('table-list-message');

    if (tableListContainer) {
        tableListContainer.addEventListener('click', function(event) {
            const targetButton = event.target.closest('.table-toggle-status-btn');
            if (!targetButton) {
                return; // Clicked outside a toggle button
            }

            event.preventDefault(); // Prevent default button action if any

            const tableId = targetButton.dataset.tableId;
            // const currentStatus = targetButton.dataset.currentStatus; // Not strictly needed for the request

            // Show a simple loading state on the button (optional)
            const originalButtonText = targetButton.innerHTML;
            targetButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Değiştiriliyor...';
            targetButton.disabled = true;

            const formData = new FormData();
            formData.append('table_id', tableId);

            fetch('/admin/tables/toggle-status', {
                method: 'POST',
                body: formData,
                headers: {
                    // 'Content-Type': 'application/x-www-form-urlencoded' // FormData sets it automatically
                    'X-Requested-With': 'XMLHttpRequest' // Good practice for server-side detection of AJAX
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update UI
                    const tableRow = targetButton.closest('tr[data-table-id="' + data.table_id + '"]');
                    if (tableRow) {
                        const statusBadge = tableRow.querySelector('td:nth-child(3) span.badge');
                        const icon = targetButton.querySelector('i');

                        if (data.new_status) { // Table is now active
                            statusBadge.classList.remove('badge-danger');
                            statusBadge.classList.add('badge-success');
                            statusBadge.textContent = 'Aktif';
                            targetButton.innerHTML = '<i class="fas fa-toggle-off"></i> Pasif Yap';
                            targetButton.dataset.currentStatus = '1';
                            icon.className = 'fas fa-toggle-off';
                        } else { // Table is now inactive
                            statusBadge.classList.remove('badge-success');
                            statusBadge.classList.add('badge-danger');
                            statusBadge.textContent = 'Pasif';
                            targetButton.innerHTML = '<i class="fas fa-toggle-on"></i> Aktif Yap';
                            targetButton.dataset.currentStatus = '0';
                            icon.className = 'fas fa-toggle-on';
                        }
                    }
                    if (tableListMessage) {
                        tableListMessage.className = 'message message-success';
                        tableListMessage.textContent = data.message || 'Durum başarıyla güncellendi.';
                        tableListMessage.style.display = 'block';
                        setTimeout(() => { tableListMessage.style.display = 'none'; }, 3000);
                    }
                } else {
                    if (tableListMessage) {
                        tableListMessage.className = 'message message-error';
                        tableListMessage.textContent = data.message || 'Durum güncellenirken bir hata oluştu.';
                        tableListMessage.style.display = 'block';
                         setTimeout(() => { tableListMessage.style.display = 'none'; }, 5000);
                    }
                    // Restore button text only on catch, if it was changed to loading
                    if (targetButton.innerHTML.includes('fa-spinner')) {
                        targetButton.innerHTML = originalButtonText;
                    }
                }
            })
            .catch(error => {
                console.error('Error toggling table status:', error);
                if (tableListMessage) {
                    tableListMessage.className = 'message message-error';
                    tableListMessage.textContent = 'Bir ağ hatası oluştu. Lütfen tekrar deneyin.';
                    tableListMessage.style.display = 'block';
                    setTimeout(() => { tableListMessage.style.display = 'none'; }, 5000);
                    // Restore button text only on catch, if it was changed to loading
                    if (targetButton.innerHTML.includes('fa-spinner')) {
                        targetButton.innerHTML = originalButtonText;
                    }
                }
            })
            .finally(() => {
                targetButton.disabled = false;
            });
        });
    }
});
</script> 