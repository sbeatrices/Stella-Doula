<?php



// Start the session for authentication
session_start();

// Define a basic routing mechanism
$route = $_GET['route'] ?? 'login';  // Default route is 'login'

// Route handling based on the value of ?route=...
switch ($route) {
    case 'register':
        include __DIR__ . '/../src/auth/register.php';
        break;
    case 'login':
        include __DIR__ . '/../src/auth/login.php';
        break;
    case 'logout':
	include __DIR__ . '/../src/auth/logout.php';
	break;
    case 'dashboard':
        include __DIR__ . '/../src/dashboard/index.php';
        break;
    case 'doula-dashboard';
	include __DIR__ . '/../src/doula/dashboard.php';
	break;
    case 'doula-profile';
	include __DIR__ . '/../src/doula/profile.php';
	break;
    case 'doula-services':
        include __DIR__ . '/../src/doula/services.php';
        break;
    case 'availability':
	include __DIR__ . '/../src/doula/availability.php';
	break;
    case 'view-profile':
	include __DIR__ . '/../src/shared/view-profile.php';
	break;
   case 'browse-doulas':
	include __DIR__ . '/../src/client/browse-doulas.php';
	break;
   case 'my-bookings':
        include __DIR__ . '/../src/client/my-bookings.php';
        break;
   case 'generate-invoice':
        include __DIR__ . '/../src/doula/invoice.php';
        break;
   case 'mark-invoiced':
        include __DIR__ . '/../src/doula/mark-invoiced.php';
        break;
   case 'view-invoice':
        include __DIR__ . '/../src/client/view-invoice.php';
        break;
    default:
        http_response_code(404);
        echo "404 Page not found.";
        break;
}
