<?php
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $imageUrl string */
/* @var $uuid int */

$this->title = 'Rate the Image';

$likeUrl = Url::to(['like']);
$dislikeUrl = Url::to(['dislike']);

$this->registerCss("
    #image-page-container {
        font-family: 'Arial', sans-serif;
        background-color: #f7f7f7;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 80vh;
        margin: 0;
    }
    #image-content {
        text-align: center;
        background-color: white;
        padding: 40px;
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        max-width: 400px;
    }
    #image-title {
        font-size: 24px;
        margin-bottom: 20px;
        color: #333;
    }
    #random-image {
        width: 100%;
        height: auto;
        max-width: 300px;
        border-radius: 10px;
        margin-bottom: 20px;
    }
    #image-buttons {
        display: flex;
        justify-content: space-around;
    }
    #like-btn, #dislike-btn {
        padding: 12px 20px;
        font-size: 16px;
        border: none;
        cursor: pointer;
        border-radius: 5px;
        transition: background-color 0.3s ease;
        min-width: 120px;
    }
    #like-btn {
        background-color: #28a745;
        color: white;
        margin-right: 10px
    }
    #like-btn:hover {
        background-color: #218838;
    }
    #dislike-btn {
        background-color: #dc3545;
        color: white;
         margin-left: 10px
    }
    #dislike-btn:hover {
        background-color: #c82333;
    }
");

$js = <<<JS
    $('#like-btn').click(function() {
        let id = $(this).attr('data-id');
        $.ajax({
            url: '$likeUrl&uuid=' + id,
            type: 'GET',
            success: function(data) {
                $('#random-image').attr('src', data.imageUrl);
                $('#like-btn').attr('data-id', data.newUUId);
                $('#dislike-btn').attr('data-id', data.newUUId);
            }
        });
    });

    $('#dislike-btn').click(function() {
        let id = $(this).attr('data-id');
        $.ajax({
            url: '$dislikeUrl&uuid=' + id,
            type: 'GET',
            success: function(data) {
                $('#random-image').attr('src', data.imageUrl);
                $('#like-btn').attr('data-id', data.newUUId);
                $('#dislike-btn').attr('data-id', data.newUUId);
            }
        });
    });
JS;

$this->registerJs($js);
?>

<div id="image-page-container">
    <div id="image-content">
        <h1 id="image-title"><?= Html::encode($this->title) ?></h1>

        <div>
            <img id="random-image" src="<?= Html::encode($imageUrl) ?>" alt="Random Image">
        </div>

        <div id="image-buttons">
			<?= Html::button('Like', ['id' => 'like-btn', 'class' => 'btn', 'data-id' => $uuid]) ?>
			<?= Html::button('Dislike', ['id' => 'dislike-btn', 'class' => 'btn', 'data-id' => $uuid]) ?>
        </div>
    </div>
</div>
