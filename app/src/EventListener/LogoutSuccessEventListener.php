<?

namespace App\EventListener;

use App\Service\LogUserService;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class LogoutSuccessEventListener
{
    public function __construct(LogUserService $log)
    {
        $this->log = $log;
    }
    
    public function onLogoutSuccess(LogoutEvent $logoutEvent): void
    {
        $user = $logoutEvent->getToken()->getUser();
        $this->log->logout($user);
    }
}
