<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config.php';

$error = '';


/*
|--------------------------------------------------------------------------
| PROGRAMMES
|--------------------------------------------------------------------------
*/

$programmes = [

    'Interdisciplinary Dual Degree Program (IDDD)',

    'International Interdisciplinary Masters Program (I2MP)',

    'Joint Masters Program (JMP)'

];


/*
|--------------------------------------------------------------------------
| VALIDATE ID
|--------------------------------------------------------------------------
*/

$id =
    isset($_GET['id'])
        ? (int)$_GET['id']
        : 0;


if ($id <= 0)
{

    header('Location: mtech.php');

    exit;

}


/*
|--------------------------------------------------------------------------
| FETCH STUDENT
|--------------------------------------------------------------------------
*/

try
{

    $sql = "
        SELECT
            id,
            programme,
            roll_number,
            student_name,
            mentor_1,
            mentor_2
        FROM mtech_students
        WHERE id = ?
        LIMIT 1
    ";


    $stmt =
        $pdo->prepare($sql);


    $stmt->execute([
        $id
    ]);


    $student =
        $stmt->fetch(
            PDO::FETCH_ASSOC
        );


    if (!$student)
    {

        header('Location: mtech.php');

        exit;

    }

}
catch (PDOException $e)
{

    header('Location: mtech.php');

    exit;

}


/*
|--------------------------------------------------------------------------
| DEFAULT VALUES
|--------------------------------------------------------------------------
*/

$programme =
    $student['programme'];

$roll_number =
    $student['roll_number'];

$student_name =
    $student['student_name'];

$mentor_1 =
    $student['mentor_1'];

$mentor_2 =
    $student['mentor_2'];


/*
|--------------------------------------------------------------------------
| HANDLE FORM SUBMISSION
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{

    $programme =
        trim($_POST['programme'] ?? '');


    $roll_number =
        trim($_POST['roll_number'] ?? '');


    $student_name =
        trim($_POST['student_name'] ?? '');


    $mentor_1 =
        trim($_POST['mentor_1'] ?? '');


    $mentor_2 =
        trim($_POST['mentor_2'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if ($programme === '')
    {

        $error =
            'Please select a programme.';

    }
    elseif (
        !in_array(
            $programme,
            $programmes,
            true
        )
    )
    {

        $error =
            'Please select a valid programme.';

    }
    elseif ($roll_number === '')
    {

        $error =
            'Please enter the roll number.';

    }
    elseif ($student_name === '')
    {

        $error =
            'Please enter the student name.';

    }


    /*
    |--------------------------------------------------------------------------
    | DUPLICATE ROLL NUMBER CHECK
    |--------------------------------------------------------------------------
    */

    if ($error === '')
    {

        try
        {

            $checkSql = "
                SELECT id
                FROM mtech_students
                WHERE roll_number = ?
                AND id <> ?
                LIMIT 1
            ";


            $checkStmt =
                $pdo->prepare($checkSql);


            $checkStmt->execute([
                $roll_number,
                $id
            ]);


            if ($checkStmt->fetch())
            {

                $error =
                    'A student with this roll number already exists.';

            }

        }
        catch (PDOException $e)
        {

            $error =
                'Unable to validate the roll number.';

        }

    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE STUDENT
    |--------------------------------------------------------------------------
    */

    if ($error === '')
    {

        try
        {

            $sql = "
                UPDATE mtech_students
                SET
                    programme = ?,
                    roll_number = ?,
                    student_name = ?,
                    mentor_1 = ?,
                    mentor_2 = ?
                WHERE id = ?
            ";


            $stmt =
                $pdo->prepare($sql);


            $stmt->execute([
                $programme,
                $roll_number,
                $student_name,
                $mentor_1,
                $mentor_2,
                $id
            ]);


            header(
                'Location: mtech.php?success=updated'
            );

            exit;

        }
        catch (PDOException $e)
        {

            $error =
                'Unable to update the student. Please try again.';

        }

    }

}


include 'header.php';

?>


<div class="dashboard-content">


    <div class="page-header">


        <div>

            <h1>Edit M.Tech Student</h1>

            <p>
                Update the M.Tech student details.
            </p>

        </div>


        <a
            href="mtech.php"
            class="btn-add"
        >

            <i class="fas fa-arrow-left"></i>

            Back to M.Tech Students

        </a>


    </div>


    <?php if ($error !== ''): ?>

        <div class="alert alert-danger">

            <?= htmlspecialchars(
                $error,
                ENT_QUOTES,
                'UTF-8'
            ) ?>

        </div>

    <?php endif; ?>


    <div class="dashboard-card">


        <div class="card-header">

            <div>

                <h3>Edit Student</h3>

                <p>
                    Update the student details below.
                </p>

            </div>

        </div>


        <div class="card-body">


            <form
                method="POST"
                action="?id=<?= $id ?>"
            >


                <!-- PROGRAMME -->

                <div class="form-group">

                    <label for="programme">

                        Programme
                        <span class="required">*</span>

                    </label>


                    <select
                        name="programme"
                        id="programme"
                        class="form-control"
                        required
                    >

                        <option value="">
                            Select Programme
                        </option>


                        <?php foreach ($programmes as $program): ?>

                            <option
                                value="<?= htmlspecialchars(
                                    $program,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                <?= (
                                    $programme === $program
                                )
                                    ? 'selected'
                                    : ''
                                ?>
                            >

                                <?= htmlspecialchars(
                                    $program,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <!-- ROLL NUMBER -->

                <div class="form-group">

                    <label for="roll_number">

                        Roll Number
                        <span class="required">*</span>

                    </label>


                    <input
                        type="text"
                        id="roll_number"
                        name="roll_number"
                        class="form-control"
                        value="<?= htmlspecialchars(
                            $roll_number,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        placeholder="Enter roll number"
                        required
                    >

                </div>


                <!-- STUDENT NAME -->

                <div class="form-group">

                    <label for="student_name">

                        Student Name
                        <span class="required">*</span>

                    </label>


                    <input
                        type="text"
                        id="student_name"
                        name="student_name"
                        class="form-control"
                        value="<?= htmlspecialchars(
                            $student_name,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        placeholder="Enter student name"
                        required
                    >

                </div>


                <!-- MENTOR 1 -->

                <div class="form-group">

                    <label for="mentor_1">
                        Mentor 1
                    </label>


                    <input
                        type="text"
                        id="mentor_1"
                        name="mentor_1"
                        class="form-control"
                        value="<?= htmlspecialchars(
                            $mentor_1,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        placeholder="Enter mentor 1"
                    >

                </div>


                <!-- MENTOR 2 -->

                <div class="form-group">

                    <label for="mentor_2">
                        Mentor 2
                    </label>


                    <input
                        type="text"
                        id="mentor_2"
                        name="mentor_2"
                        class="form-control"
                        value="<?= htmlspecialchars(
                            $mentor_2,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        placeholder="Enter mentor 2"
                    >

                </div>


                <!-- BUTTONS -->

                <div class="form-actions">


                    <button
                        type="submit"
                        class="btn-confirm-delete"
                        style="background: #184C74;"
                    >

                        <i class="fas fa-save"></i>

                        Update Student

                    </button>


                    <a
                        href="mtech.php"
                        class="btn-cancel"
                    >

                        Cancel

                    </a>


                </div>


            </form>

        </div>

    </div>

</div>


<?php include 'footer.php'; ?>