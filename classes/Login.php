
<?php
require_once '../config.php';

class Login extends DBConnection
{
    private $settings;

    public function __construct()
    {
        global $_settings;
        $this->settings = $_settings;
        parent::__construct();

        // Do not display PHP errors to users.
        ini_set('display_errors', '0');
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    public function index()
    {
        http_response_code(403);
        echo "Access denied.";
    }

    /**
     * Verify a password and upgrade a legacy MD5 hash
     * to password_hash() after successful authentication.
     */
    private function verifyPassword($table, $id, $storedPassword, $password)
    {
        // Modern password hash
        if (password_verify($password, $storedPassword)) {
            if (password_needs_rehash(
                $storedPassword,
                PASSWORD_DEFAULT
            )) {
                $newHash = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                $stmt = $this->conn->prepare(
                    "UPDATE `$table` SET `password` = ? WHERE id = ?"
                );

                if ($stmt) {
                    $stmt->bind_param("si", $newHash, $id);
                    $stmt->execute();
                    $stmt->close();
                }
            }

            return true;
        }

        // Temporary compatibility for existing MD5 passwords.
        // A successful legacy login upgrades the stored password.
        if (
            preg_match('/^[a-f0-9]{32}$/i', $storedPassword) &&
            hash_equals(
                strtolower($storedPassword),
                md5($password)
            )
        ) {
            $newHash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            if (!$newHash) {
                return false;
            }

            $stmt = $this->conn->prepare(
                "UPDATE `$table` SET `password` = ? WHERE id = ?"
            );

            if (!$stmt) {
                return false;
            }

            $stmt->bind_param("si", $newHash, $id);
            $updated = $stmt->execute();
            $stmt->close();

            return $updated;
        }

        return false;
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return json_encode([
                'status' => 'failed',
                'msg' => 'Method not allowed.'
            ]);
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (
            $username === '' ||
            $password === '' ||
            strlen($username) > 100 ||
            strlen($password) > 4096
        ) {
            return json_encode([
                'status' => 'incorrect',
                'msg' => 'Invalid username or password.'
            ]);
        }

        $stmt = $this->conn->prepare(
            "SELECT * FROM users WHERE username = ? LIMIT 1"
        );

        if (!$stmt) {
            http_response_code(500);
            return json_encode([
                'status' => 'failed',
                'msg' => 'Unable to process login.'
            ]);
        }

        $stmt->bind_param("s", $username);

        if (!$stmt->execute()) {
            $stmt->close();
            http_response_code(500);

            return json_encode([
                'status' => 'failed',
                'msg' => 'Unable to process login.'
            ]);
        }

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if (
            !$user ||
            !$this->verifyPassword(
                'users',
                (int) $user['id'],
                $user['password'],
                $password
            )
        ) {
            return json_encode([
                'status' => 'incorrect',
                'msg' => 'Invalid username or password.'
            ]);
        }

        if ((int) $user['status'] !== 1) {
            return json_encode([
                'status' => 'notverified'
            ]);
        }

        // Prevent session fixation after successful login.
        session_regenerate_id(true);

        foreach ($user as $key => $value) {
            if (!is_numeric($key) && $key !== 'password') {
                $this->settings->set_userdata($key, $value);
            }
        }

        $this->settings->set_userdata('login_type', 1);

        return json_encode([
            'status' => 'success'
        ]);
    }

    public function logout()
    {
        $this->settings->sess_des();

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }

        redirect('admin/login.php');
    }

    public function employee_login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);

            return json_encode([
                'status' => 'failed',
                'msg' => 'Method not allowed.'
            ]);
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (
            $email === '' ||
            $password === '' ||
            strlen($email) > 254 ||
            strlen($password) > 4096
        ) {
            return json_encode([
                'status' => 'failed',
                'msg' => 'Invalid email or password.'
            ]);
        }

        $stmt = $this->conn->prepare(
            "SELECT *,
             CONCAT(lastname, ', ', firstname, ' ', middlename)
             AS fullname
             FROM employee_list
             WHERE email = ?
             LIMIT 1"
        );

        if (!$stmt) {
            http_response_code(500);

            return json_encode([
                'status' => 'failed',
                'msg' => 'Unable to process login.'
            ]);
        }

        $stmt->bind_param("s", $email);

        if (!$stmt->execute()) {
            $stmt->close();
            http_response_code(500);

            return json_encode([
                'status' => 'failed',
                'msg' => 'Unable to process login.'
            ]);
        }

        $result = $stmt->get_result();
        $employee = $result->fetch_assoc();
        $stmt->close();

        if (
            !$employee ||
            !$this->verifyPassword(
                'employee_list',
                (int) $employee['id'],
                $employee['password'],
                $password
            )
        ) {
            return json_encode([
                'status' => 'failed',
                'msg' => 'Invalid email or password.'
            ]);
        }

        if ((int) $employee['status'] !== 1) {
            return json_encode([
                'status' => 'failed',
                'msg' => 'Your account is inactive.'
            ]);
        }

        session_regenerate_id(true);

        foreach ($employee as $key => $value) {
            if (!is_numeric($key) && $key !== 'password') {
                $this->settings->set_userdata($key, $value);
            }
        }

        $this->settings->set_userdata('login_type', 2);

        return json_encode([
            'status' => 'success'
        ]);
    }

    public function employee_logout()
    {
        $this->settings->sess_des();

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }

        redirect('./login.php');
    }
}

$action = strtolower($_GET['f'] ?? 'none');
$auth = new Login();

switch ($action) {
    case 'login':
        echo $auth->login();
        break;

    case 'logout':
        echo $auth->logout();
        break;

    case 'elogin':
        echo $auth->employee_login();
        break;

    case 'elogout':
        echo $auth->employee_logout();
        break;

    default:
        $auth->index();
        break;
}
