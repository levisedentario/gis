<?php

require_once __DIR__ . "/../config/db.php";

/*
|--------------------------------------------------------------------------
| DELETE
|--------------------------------------------------------------------------
*/

if (
    isset($_GET['action']) &&
    $_GET['action'] === 'delete' &&
    isset($_GET['id'])
) {

    $id = intval($_GET['id']);

    $sql = "DELETE FROM garbage_accumulation WHERE id = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {

        mysqli_stmt_close($stmt);

        header("Location: ../garbage_accumulation.php?message=deleted");
        exit;

    } else {

        mysqli_stmt_close($stmt);

        die("Error deleting record: " . mysqli_error($conn));
    }
}


/*
|--------------------------------------------------------------------------
| EDIT / UPDATE
|--------------------------------------------------------------------------
*/

if (
    isset($_POST['action']) &&
    $_POST['action'] === 'edit'
) {

    $id = intval($_POST['id']);

    $manhole = trim($_POST['manhole']);
    $nbase = floatval($_POST['nbase']);
    $nmodified = floatval($_POST['nmodified']);
    $ngarbage = floatval($_POST['ngarbage']);
    $flood_susceptibility = trim($_POST['flood_susceptibility']);


    $sql = "
        UPDATE garbage_accumulation
        SET
            manhole = ?,
            nbase = ?,
            nmodified = ?,
            ngarbage = ?,
            flood_susceptibility = ?
        WHERE id = ?
    ";


    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "sdddsi",
        $manhole,
        $nbase,
        $nmodified,
        $ngarbage,
        $flood_susceptibility,
        $id
    );


    if (mysqli_stmt_execute($stmt)) {

        mysqli_stmt_close($stmt);

        header("Location: ../garbage_accumulation.php?message=updated");
        exit;

    } else {

        mysqli_stmt_close($stmt);

        die("Error updating record: " . mysqli_error($conn));
    }
}


/*
|--------------------------------------------------------------------------
| INVALID REQUEST
|--------------------------------------------------------------------------
*/

header("Location: ../garbage_accumulation.php");
exit;

?>