<?php
require_once "config/db.php";

// Search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if ($search !== '') {

    $search_safe = mysqli_real_escape_string($conn, $search);

    $sql = "
        SELECT * FROM garbage_accumulation
        WHERE
            manhole LIKE '%$search_safe%'
            OR nbase LIKE '%$search_safe%'
            OR nmodified LIKE '%$search_safe%'
            OR ngarbage LIKE '%$search_safe%'
            OR flood_susceptibility LIKE '%$search_safe%'
        ORDER BY id ASC
    ";

} else {

    $sql = "
        SELECT * FROM garbage_accumulation
        ORDER BY id ASC
    ";
}

$result = mysqli_query($conn, $sql);


// Edit mode
$edit_data = null;

if (isset($_GET['edit'])) {

    $id = intval($_GET['edit']);

    $edit_result = mysqli_query(
        $conn,
        "SELECT * FROM garbage_accumulation WHERE id = $id"
    );

    if ($edit_result && mysqli_num_rows($edit_result) > 0) {
        $edit_data = mysqli_fetch_assoc($edit_result);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Garbage Accumulation</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 20px;
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f6f8;
            font-size: 13px;
        }

        /* FULL PAGE CONTAINER */

        .container {
            width: 100%;
            max-width: none;
            margin: 0;
            background: #ffffff;
            padding: 18px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        /* HEADER */

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .page-header h2 {
            margin: 0;
            font-size: 20px;
        }

        .btn-admin {
            background: #343a40;
            color: white;
            padding: 7px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 12px;
        }

        .btn-admin:hover {
            background: #212529;
        }

        /* SEARCH */

        .search-container {
            display: flex;
            gap: 8px;
            margin-bottom: 15px;
        }

        .search-container input {
            width: 300px;
            padding: 8px 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 13px;
        }

        .search-btn {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            background: #0d6efd;
            color: white;
            cursor: pointer;
            font-size: 13px;
        }

        .clear-btn {
            padding: 8px 15px;
            border-radius: 5px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            font-size: 13px;
        }

        /* EDIT BOX */

        .edit-box {
            background: #f8f9fa;
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 6px;
        }

        .edit-box h3 {
            margin-top: 0;
            font-size: 16px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 4px;
            font-size: 12px;
            font-weight: bold;
        }

        input,
        select {
            padding: 7px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 12px;
        }

        .form-buttons {
            margin-top: 12px;
        }

        /* BUTTONS */

        .btn {
            border: none;
            padding: 6px 10px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 11px;
        }

        .btn-save {
            background: #198754;
            color: white;
        }

        .btn-edit {
            background: #0d6efd;
            color: white;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
        }

        .btn-cancel {
            background: #6c757d;
            color: white;
        }

        /* TABLE */

        .table-container {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
            font-size: 12px;
        }

        th {
            background: #212529;
            color: white;
            padding: 9px 8px;
            text-align: left;
            font-size: 11px;
            position: sticky;
            top: 0;
            z-index: 2;
        }

        td {
            padding: 7px 8px;
            border-bottom: 1px solid #e1e1e1;
            font-size: 12px;
            white-space: nowrap;
        }

        tbody tr:hover {
            background: #f5f7fa;
        }

        /* ACTION COLUMN */

        .actions {
            white-space: nowrap;
        }

        /* SUSCEPTIBILITY */

        .susceptibility {
            font-weight: bold;
        }

        /* MOBILE */

        @media (max-width: 900px) {

            body {
                padding: 10px;
            }

            .container {
                padding: 12px;
            }

            .form-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .search-container input {
                width: 100%;
            }

        }

        @media (max-width: 600px) {

            .page-header {
                display: block;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .search-container {
                flex-wrap: wrap;
            }

        }

    </style>

</head>

<body>

<div class="container">

        <div class="page-header">

            <h2>Garbage Accumulation Data</h2>

            <a href="index.php" class="btn-admin">
                ← Back to Admin
            </a>

        </div>


    <!-- SEARCH -->

    <form
        method="GET"
        action="garbage_accumulation.php"
        class="search-container"
    >

        <input
            type="text"
            name="search"
            placeholder="Search manhole, susceptibility, Ngarbage..."
            value="<?= htmlspecialchars($search) ?>"
        >

        <button
            type="submit"
            class="search-btn"
        >
            Search
        </button>

        <?php if ($search !== ''): ?>

            <a
                href="garbage_accumulation.php"
                class="clear-btn"
            >
                Clear
            </a>

        <?php endif; ?>

    </form>


    <!-- EDIT FORM -->

    <?php if ($edit_data): ?>

        <div class="edit-box">

            <h3>Edit Garbage Accumulation</h3>

            <form
                action="controllers/ga_controller.php"
                method="POST"
            >

                <input
                    type="hidden"
                    name="action"
                    value="edit"
                >

                <input
                    type="hidden"
                    name="id"
                    value="<?= $edit_data['id'] ?>"
                >

                <div class="form-grid">

                    <div class="form-group">

                        <label>Manhole</label>

                        <input
                            type="text"
                            name="manhole"
                            value="<?= htmlspecialchars($edit_data['manhole']) ?>"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>N Base</label>

                        <input
                            type="number"
                            step="0.001"
                            name="nbase"
                            value="<?= $edit_data['nbase'] ?>"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>N Modified</label>

                        <input
                            type="number"
                            step="0.001"
                            name="nmodified"
                            value="<?= $edit_data['nmodified'] ?>"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>N Garbage</label>

                        <input
                            type="number"
                            step="0.001"
                            name="ngarbage"
                            value="<?= $edit_data['ngarbage'] ?>"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>Flood Susceptibility</label>

                        <select
                            name="flood_susceptibility"
                            required
                        >

                            <option value="Low"
                                <?= $edit_data['flood_susceptibility'] == 'Low' ? 'selected' : '' ?>>
                                Low
                            </option>

                            <option value="Moderate"
                                <?= $edit_data['flood_susceptibility'] == 'Moderate' ? 'selected' : '' ?>>
                                Moderate
                            </option>

                            <option value="High"
                                <?= $edit_data['flood_susceptibility'] == 'High' ? 'selected' : '' ?>>
                                High
                            </option>

                        </select>

                    </div>

                </div>


                <div class="form-buttons">

                    <button
                        type="submit"
                        class="btn btn-save"
                    >
                        Save Changes
                    </button>

                    <a
                        href="garbage_accumulation.php"
                        class="btn btn-cancel"
                    >
                        Cancel
                    </a>

                </div>

            </form>

        </div>

    <?php endif; ?>


    <!-- TABLE -->

    <div class="table-container">

        <table>

            <thead>

                <tr>

                    <th>ID</th>

                    <th>Manhole</th>

                    <th>N Base</th>

                    <th>N Modified</th>

                    <th>N Garbage</th>

                    <th>Flood Susceptibility</th>

                    <th>Action</th>

                </tr>

            </thead>


            <tbody>

            <?php if ($result && mysqli_num_rows($result) > 0): ?>

                <?php while ($row = mysqli_fetch_assoc($result)): ?>

                    <tr>

                        <td>
                            <?= $row['id'] ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['manhole']) ?>
                        </td>

                        <td>
                            <?= number_format($row['nbase'], 3) ?>
                        </td>

                        <td>
                            <?= number_format($row['nmodified'], 3) ?>
                        </td>

                        <td>
                            <?= number_format($row['ngarbage'], 3) ?>
                        </td>

                        <td class="susceptibility">

                            <?= htmlspecialchars(
                                $row['flood_susceptibility']
                            ) ?>

                        </td>

                        <td class="actions">

                            <a
                                href="garbage_accumulation.php?edit=<?= $row['id'] ?>"
                                class="btn btn-edit"
                            >
                                Edit
                            </a>

                            <a
                                href="controllers/ga_controller.php?action=delete&id=<?= $row['id'] ?>"
                                class="btn btn-delete"
                                onclick="return confirm('Are you sure you want to delete this record?');"
                            >
                                Delete
                            </a>

                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>

                    <td
                        colspan="7"
                        style="text-align:center; padding:20px;"
                    >
                        No records found.
                    </td>

                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

</body>

</html>