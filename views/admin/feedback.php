<?php
// Admin Feedback List View
?>

<h2><?php echo htmlspecialchars($pageTitle); ?></h2>

<?php 
// Hata mesajını göster (varsa)
if (isset($error_message) && $error_message): 
?>
    <div class="message message-error">
        <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error_message); ?>
    </div>
<?php endif; ?>

<div style="margin-bottom: 15px; text-align: right;">
    <form method="POST" action="/admin/feedback/deleteAll" onsubmit="return confirm('Tüm geri bildirimleri silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.');">
        <button type="submit" class="button button-danger" style="padding: 8px 15px; font-size: 0.9em;">
            <i class="fas fa-trash-alt"></i> Tüm Geri Bildirimleri Sil
        </button>
    </form>
</div>

<div class="orders-table-container card">
    <div class="card-body" style="padding: 0;">
        <?php if (empty($feedbacks)): ?>
            <p style="padding: 15px; text-align: center;">Gösterilecek geri bildirim bulunamadı.</p>
        <?php else: ?>
            <table class="orders-table feedback-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ad Soyad</th>
                        <th>E-posta</th>
                        <th>Mesaj</th>
                        <th>Gönderilme Tarihi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($feedbacks as $feedback): ?>
                        <?php $fullMessage = $feedback->message ?? ''; ?>
                        <tr>
                            <td data-label="ID"><?php echo $feedback->id; ?></td>
                            <td data-label="Ad Soyad"><?php echo htmlspecialchars($feedback->name); ?></td>
                            <td data-label="E-posta"><a href="mailto:<?php echo htmlspecialchars($feedback->email); ?>"><?php echo htmlspecialchars($feedback->email); ?></a></td>
                            <td data-label="Mesaj" class="feedback-message-cell" data-full-message="<?php echo htmlspecialchars($fullMessage); /* Tam mesajı attribute'e ekle */ ?>">
                                <?php 
                                $maxLength = 250;
                                echo htmlspecialchars(mb_substr($fullMessage, 0, $maxLength, 'UTF-8')); 
                                if (mb_strlen($fullMessage, 'UTF-8') > $maxLength) {
                                    echo '...';
                                }
                                ?>
                            </td>
                            <td data-label="Tarih">
                                <?php 
                                try {
                                    // Tarihi formatla (Örn: 08 May 2025 15:30)
                                    $date = new DateTime($feedback->created_at);
                                    // Gerekirse farklı format kullanılabilir: $date->format('d.m.Y H:i')
                                    // Ancak IntlDateFormatter daha lokalizasyon dostu olabilir
                                    if (class_exists('IntlDateFormatter')) {
                                        $formatter = new IntlDateFormatter('tr_TR', IntlDateFormatter::MEDIUM, IntlDateFormatter::SHORT);
                                        echo $formatter->format($date);
                                    } else {
                                        echo $date->format('d M Y H:i'); // Fallback format
                                    }
                                } catch (Exception $e) {
                                    echo htmlspecialchars($feedback->created_at); // Formatlama hatası olursa ham veriyi göster
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Tooltip için Gizli Div -->
<div id="feedback-tooltip" style="position: absolute; display: none; background-color: #333; color: white; padding: 8px 12px; border-radius: 4px; z-index: 1070; max-width: 400px; word-wrap: break-word; font-size: 0.9em; box-shadow: 0 2px 5px rgba(0,0,0,0.2); pointer-events: none; /* Fare olaylarını engelle */ line-height: 1.4;"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tooltip = document.getElementById('feedback-tooltip');
    const messageCells = document.querySelectorAll('td.feedback-message-cell');

    if (!tooltip) return;

    messageCells.forEach(cell => {
        cell.addEventListener('mouseover', function(e) {
            const fullMessage = this.dataset.fullMessage;
            if (fullMessage) {
                tooltip.innerHTML = fullMessage.replace(/\n/g, '<br>');
                tooltip.style.display = 'block';
                positionTooltip(e);
            }
        });

        cell.addEventListener('mousemove', function(e) {
            if (tooltip.style.display === 'block') {
                positionTooltip(e);
            }
        });

        cell.addEventListener('mouseout', function() {
            tooltip.style.display = 'none';
            tooltip.innerHTML = '';
        });
    });

    function positionTooltip(e) {
        // const tooltip = document.getElementById('feedback-tooltip'); // Zaten yukarıda tanımlı
        // if (!tooltip) return;

        const offsetX = 15;
        const offsetY = 10;
        let desiredX = e.pageX + offsetX;
        let desiredY = e.pageY + offsetY;

        tooltip.style.left = '0px'; // Reset position for accurate measurement
        tooltip.style.top = '0px';
        const tooltipRect = tooltip.getBoundingClientRect();
        const tooltipWidth = tooltipRect.width;
        const tooltipHeight = tooltipRect.height;

        const viewportWidth = window.innerWidth;
        const viewportHeight = window.innerHeight;
        const scrollX = window.scrollX;
        const scrollY = window.scrollY;
        
        // Adjust right overflow
        if (desiredX + tooltipWidth > scrollX + viewportWidth - 10) {
            desiredX = e.pageX - tooltipWidth - offsetX;
        }

        // Adjust left overflow
        if (desiredX < scrollX + 10) {
            desiredX = scrollX + 10;
        }

        // Adjust bottom overflow
        if (desiredY + tooltipHeight > scrollY + viewportHeight - 10) {
            desiredY = e.pageY - tooltipHeight - offsetY;
        }

        // Adjust top overflow
        if (desiredY < scrollY + 10) {
            desiredY = scrollY + 10;
        }

        tooltip.style.left = `${desiredX}px`;
        tooltip.style.top = `${desiredY}px`;
    }
});
</script> 