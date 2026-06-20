<?php if ($is_public_layout ?? false): ?>
    </main>
    <footer class="public-footer">
        <span>&copy; <?= date('Y'); ?> BookSphere Library System</span>
        <span>UAS PPW1</span>
    </footer>
</div>
<?php else: ?>
        </main>
        <footer class="app-footer">
            <span>&copy; <?= date('Y'); ?> BookSphere Library System</span>
            <span>Ujian Akhir Semester</span>
        </footer>
    </div>
</div>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e(base_url('assets/js/validasi.js')); ?>"></script>
<script src="<?= e(base_url('assets/js/interaktif.js')); ?>"></script>
</body>
</html>