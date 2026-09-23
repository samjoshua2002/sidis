<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config.php';


$error = '';


$id =
    $_GET['id'] ??
    $_POST['id'] ??
    '';


/*
|--------------------------------------------------------------------------
| VALIDATE ID
|--------------------------------------------------------------------------
*/

if (
    $id === '' ||
    !ctype_digit((string)$id)
)
{

    header('Location: clusters.php');

    exit;

}


$id = (int)$id;


/*
|--------------------------------------------------------------------------
| FETCH EXISTING CLUSTER
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id,
        cluster_name,
        status
    FROM clusters
    WHERE id = ?
    LIMIT 1
");


$stmt->execute([$id]);


$cluster =
    $stmt->fetch(PDO::FETCH_ASSOC);


if (!$cluster)
{

    header('Location: clusters.php');

    exit;

}


/*
|--------------------------------------------------------------------------
| DEFAULT FORM VALUES
|--------------------------------------------------------------------------
*/

$cluster_name =
    $cluster['cluster_name'];


/*
|--------------------------------------------------------------------------
| PROCESS UPDATE
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{

    $cluster_name =
        trim($_POST['cluster_name'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if ($cluster_name === '')
    {

        $error =
            'Please enter the cluster name.';

    }


    /*
    |--------------------------------------------------------------------------
    | CHECK DUPLICATE
    |--------------------------------------------------------------------------
    */

    if ($error === '')
    {

        $stmt = $pdo->prepare("
            SELECT
                id
            FROM clusters
            WHERE cluster_name = ?
            AND id != ?
            LIMIT 1
        ");


        $stmt->execute([
            $cluster_name,
            $id
        ]);


        if ($stmt->fetch())
        {

            $error =
                'This cluster already exists.';

        }

    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    if ($error === '')
    {

        try
        {

            $stmt = $pdo->prepare("
                UPDATE clusters
                SET
                    cluster_name = ?,
                    status = 1
                WHERE id = ?
            ");


            $stmt->execute([
                $cluster_name,
                $id
            ]);


            /*
            |--------------------------------------------------------------------------
            | REDIRECT BEFORE HEADER.PHP
            |--------------------------------------------------------------------------
            */

            header(
                'Location: clusters.php?success=updated'
            );

            exit;

        }
        catch (PDOException $e)
        {

            $error =
                'Unable to update the cluster. Please try again.';

        }

    }

}


/*
|--------------------------------------------------------------------------
| HEADER
|--------------------------------------------------------------------------
*/

include 'header.php';

?>


<div class="dashboard-content">


    <!-- PAGE HEADER -->

    <div class="page-header">

        <div>

            <h2>Edit Cluster</h2>

            <p>
                Update the cluster details.
            </p>

        </div>

    </div>


    <!-- ERROR -->

    <?php if ($error !== ''): ?>

        <div class="alert alert-danger">

            <?= htmlspecialchars(
                $error,
                ENT_QUOTES,
                'UTF-8'
            ) ?>

        </div>

    <?php endif; ?>


    <!-- FORM CARD -->

    <div class="dashboard-card">


        <div class="card-header">

            <div>

                <h3>
                    Edit Cluster 
                </h3>

                <p>
                    Update the cluster details.
                </p>

            </div>

        </div>


        <form
            method="POST"
            action=""
        >


            <input
                type="hidden"
                name="id"
                value="<?= $id ?>"
            >


            <!-- CLUSTER NAME -->

            <div class="form-group">

                <label for="cluster_name">
                    Cluster Name
                </label>


                <input
                    type="text"
                    id="cluster_name"
                    name="cluster_name"
                    class="form-control"
                    value="<?= htmlspecialchars(
                        $cluster_name,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    placeholder="Enter cluster name"
                    required
                >

            </div>


            <!-- BUTTONS -->

            <div class="form-actions">

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Update Cluster
                </button>


                <a
                    href="clusters.php"
                    class="btn btn-secondary"
                >
                    Cancel
                </a>

            </div>


        </form>

    </div>

</div>


<?php include 'footer.php'; ?>