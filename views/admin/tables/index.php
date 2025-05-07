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