<?php 
// Ana Sayfa View
?>

<div class="hero-section" style="text-align: center; padding: 40px 20px;">
    <h2><i class="fas fa-birthday-cake"></i> Pastanemize Hoş Geldiniz!</h2>
    <p>Lezzetli ve taze ürünlerimizi keşfedin. Hızlı ve kolay sipariş için menümüze göz atın!</p>
    <a href="/menu" class="btn btn-primary" style="background-color: var(--accent-color); border-color: var(--accent-color); color: var(--white);"><i class="fas fa-book-open"></i> Menüyü İncele</a>
</div>

<hr style="margin: 40px 0;">

<div id="feedback-section" class="feedback-section card" style="max-width: 600px; margin: 20px auto;">
    <div class="card-header">
        <h3><i class="fas fa-comments"></i> Geri Bildirimde Bulunun</h3>
    </div>
    <div class="card-body">
        <?php // Display flash message if exists
        if (isset($flash_message)):
        ?>
            <div class="message message-<?php echo htmlspecialchars($flash_message['type']); ?>">
                <?php echo htmlspecialchars($flash_message['text']); ?>
            </div>
        <?php endif; ?>

        <p>Görüşleriniz bizim için değerlidir. Lütfen aşağıdaki formu doldurarak bize ulaşın.</p>
        
        <form action="/feedback/submit" method="POST"> <?php // Action güncellendi ?>
            <div class="form-group">
                <label for="feedback_name">Adınız:</label>
                <input type="text" id="feedback_name" name="name" value="<?php echo htmlspecialchars($old['name'] ?? ''); ?>" required>
                <?php if (isset($errors['name'])): ?><small class="form-error-text"><?php echo htmlspecialchars($errors['name']); ?></small><?php endif; ?>
            </div>
            <div class="form-group">
                <label for="feedback_email">E-posta Adresiniz:</label>
                <input type="email" id="feedback_email" name="email" value="<?php echo htmlspecialchars($old['email'] ?? ''); ?>" required>
                <?php if (isset($errors['email'])): ?><small class="form-error-text"><?php echo htmlspecialchars($errors['email']); ?></small><?php endif; ?>
            </div>
            <div class="form-group">
                <label for="feedback_message">Mesajınız:</label>
                <textarea id="feedback_message" name="message" rows="5" required><?php echo htmlspecialchars($old['message'] ?? ''); ?></textarea>
                <?php if (isset($errors['message'])): ?><small class="form-error-text"><?php echo htmlspecialchars($errors['message']); ?></small><?php endif; ?>
            </div>
            <button type="submit" class="btn btn-secondary"><i class="fas fa-paper-plane"></i> Gönder</button> <?php // disabled kaldırıldı ?>
        </form>
    </div>
</div> 