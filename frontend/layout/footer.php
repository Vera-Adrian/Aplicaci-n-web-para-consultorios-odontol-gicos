        </main>
    </div>
</div>

<script src="<?= BASE_URL ?>frontend/assets/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>frontend/assets/js/main.js"></script>
<script src="<?= BASE_URL ?>frontend/assets/js/buscador.js"></script>
<?php foreach ($scripts ?? [] as $script): ?>
    <script src="<?= BASE_URL ?>frontend/assets/js/<?= e($script) ?>"></script>
<?php endforeach; ?>
</body>
</html>