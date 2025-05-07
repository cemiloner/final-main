<?php
$pageTitle = 'Ana Menü';
?>

<div class="main-menu-container">
    <div class="menu-buttons">
        <a href="/menu" class="order-button">
            <i class="fas fa-shopping-cart"></i>
            <span>Sipariş Ver</span>
        </a>
        
        <a href="/feedback" class="feedback-button">
            <i class="fas fa-comment"></i>
            <span>Geribildirim Ver</span>
        </a>
    </div>
</div>

<style>
    .main-menu-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 80vh;
        padding: 20px;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    }
    
    .menu-buttons {
        display: flex;
        gap: 20px;
        justify-content: center;
    }
    
    .order-button, .feedback-button {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 200px;
        height: 200px;
        border-radius: 15px;
        background: white;
        text-decoration: none;
        color: #333;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        padding: 20px;
    }
    
    .order-button {
        background: linear-gradient(45deg, #4CAF50, #45a049);
        color: white;
    }
    
    .feedback-button {
        background: linear-gradient(45deg, #2196F3, #1976D2);
        color: white;
    }
    
    .order-button:hover, .feedback-button:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 12px rgba(0, 0, 0, 0.15);
    }
    
    .order-button i, .feedback-button i {
        font-size: 2.5em;
        margin-bottom: 10px;
    }
    
    .order-button span, .feedback-button span {
        font-size: 1.2em;
        font-weight: 500;
    }
</style>
