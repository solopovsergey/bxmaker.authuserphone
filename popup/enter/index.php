<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.js"
            integrity="sha512-n/4gHW3atM3QqRcbCn6ewmpxcLAHGaDjpEBu4xZd47N0W2oQ+6q7oc3PXstrJYXcbNU1OHdQ1T7pAP+gi5Yu8g=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js"
            integrity="sha512-uURl+ZXMBrF4AwGaWmEetzrd+J5/8NRkWAvJx5sbPSSuOb0bZLqf+tOzniObO00BjHa/dD7gub9oCGMLPQHtQA=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css"
          integrity="sha512-H9jrZiiopUdsLpg94A333EfumgUBpO9MdbxStdeITo+KEIMaNfHNvwyjjDJb+ERPaRS6DpyRlKbvPUasNItRyw=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>

    <div class="example-auth">
        <?php
        if ($USER->IsAuthorized()) {
            ?>
            <div>Вы успешно авторизованы</div>
            <div>
                <a href="?logout=y">Выйти</a>
            </div>
            <?php
        } else {
            ?>
            <button type="button" class="example-auth__btn example-auth__btn--auth">Вход</button>
            <button type="button" class="example-auth__btn example-auth__btn--reg">Регистрация</button>
            <?php
        }
        ?>
    </div>


    <style>
        .example-auth {
            display: block;
            width: 300px;
            text-align: center;
            margin: 100px auto;
            padding: 32px;
            box-shadow: 0 0 8px 0 rgba(0, 0, 0, 0.1);
        }

        .example-auth__btn {
            min-width: 112px;
            margin: 0 0.5em;
            width: auto;
            text-align: center;
            display: inline-block;
            cursor: pointer;
            background-color: #338eef;
            border: 1px solid;
            border-radius: 5px;
            color: #ffffff;
            font-size: 15px;
            font-weight: bold;
            padding: 0.6em 1.4em;
        }

        .example-auth__btn:hover {
            background-color: #1174de;
            border-color: #1b81ed #116fd4 #106dcf #1b81ed;
        }

    </style>

    <script>
        $(document).ready(function () {

            $(document).on("click", ".example-auth__btn--auth", function (event) {
                event.preventDefault();
                event.stopPropagation();

                location.hash = '';

                $.fancybox.open({
                    loop: true,
                    gutter: 0,
                    closeBtn: true,
                    arrows: false,
                    keyboard: false,
                    infobar: false,
                    toolbar: false,
                    protect: false,
                    buttons: ['close'],
                    smallBtn: false,
                    touch: false,
                    type: 'ajax',
                    src: 'auth.php?ncc=y',
                });
            });

            $(document).on("click", ".example-auth__btn--reg", function (event) {
                event.preventDefault();
                event.stopPropagation();

                location.hash = '#reg';

                $.fancybox.open({
                    loop: true,
                    gutter: 0,
                    closeBtn: true,
                    arrows: false,
                    keyboard: false,
                    infobar: false,
                    toolbar: false,
                    protect: false,
                    buttons: ['close'],
                    smallBtn: false,
                    touch: false,
                    type: 'ajax',
                    src: 'auth.php?ncc=y',
                });
            });

        })
    </script>


<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>