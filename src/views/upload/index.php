<?php require_once ROOT . '/views/layouts/header.php'; ?>
    <?php if (isset($error)) { ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong>Error!</strong> <?= $error['message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php } ?>
    <h2 class="mt-5">Load data for analysis</h2>
    <form action="/upload/data" method="post" enctype="multipart/form-data">
        <div>
            <label for="data" class="form-label">Enter file (txt or dat):</label>
            <input class="form-control" type="file" accept=".txt, .dat" required name="data" id="data">
        </div>
        <div>
            <input type="submit" class="btn btn-dark mt-3" value="Load">
        </div>
    </form>
<?php require_once ROOT . '/views/layouts/footer.php'; ?>