<?php

namespace Pitch7900\SessionsHandler;

use SessionHandler;
use Pitch7900\Database\Sessions;

/**
 * Description of MySqlSessionsHandler
 * @link https://www.php.net/manual/fr/class.sessionhandlerinterface.php
 * @author Pierre Christensen
 */
class DBSessionsHandler  extends SessionHandler
{
    private $logfile;
    private $debug;
    private $session_duration;
    private $authenticatedUserValue;
    private $noAuthMaxLifetTime = 30;

    /**
     * __construct
     *
     * @param  mixed $session_duration
     * @param  mixed $authenticatedUserValue
     * @param  mixed $logfile
     * @param  mixed $debug
     * @return void
     */
    public function __construct(int $session_duration = 3600, $authenticatedUserValue = null, string $logfile = null, bool $debug = false)
    {
        $this->session_duration = $session_duration;
        $this->debug = $debug;
        $this->logfile = $logfile;
        $this->authenticatedUserValue = $authenticatedUserValue;
    }


    /**
     * open
     * @link  https://www.php.net/manual/fr/sessionhandler.open.php
     * @param  mixed $savePath
     * @param  mixed $sessionName
     * @return void
     */
    public function open($savePath, $sessionName)
    {
        $this->log('open(' . $savePath . ', ' . $sessionName . ')');
        $authlimit = time() - ($this->session_duration);
        $noauthlimit = time() - $this->noAuthMaxLifetTime;
        // remove all session that are expired
        Sessions::where('timestamp', '<', $authlimit)->forceDelete();
        $sessions = Sessions::all();
        // remove all sessions that are not authentified and which lifetime is > noAuthMaxLifetTime
        // This will avoid to retain sessions that are not authentified (Loab balancer or others robots)
        foreach ($sessions as $session) {
            if (!$this->isAuthentified($session->data) && $session->data < $noauthlimit) {
                $session->forceDelete();
            }
        }
        return true;
    }

    /**
     * close
     * @link https://www.php.net/manual/fr/sessionhandler.close.php
     * @return void
     */
    public function close()
    {
        $this->log('close');
        return true;
    }

    public function getSesssionArray(string $data)
    {
        $this->log('getSesssionArray()' . $data);
        if (is_null($data)) {
            return array();
        }
        $currentSession = array_map(function ($a) {
            return $a;
        }, CustomSession::unserialize($data));

        $this->log('getSesssionArray()' . var_export($currentSession, true));
        return $currentSession;
    }

    /**
     * Check if the session hold an authentication token
     */
    public function isAuthentified(string $data): bool
    {
        if (is_null($this->authenticatedUserValue)) {
            return false;
        }
        $currentSession = $this->getSesssionArray($data);
        if (isset($currentSession[$this->authenticatedUserValue])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * read https://www.php.net/manual/fr/sessionhandler.read.php
     * @link 
     * @param  mixed $id
     * @return string
     */
    public function read($id): string
    {
        $this->log('read(' . $id . ')');
        $session = Sessions::find($id);

        if (!is_null($session)) {
            //populates the $_SESSION superglobal
            //Necessary if using multiple servers in backend
            session_decode($session->data);
            return (string)$session->data;
        } else {
            return "";
        }
    }

    /**
     * write
     * @link https://www.php.net/manual/fr/sessionhandler.write.php
     * @param  mixed $id
     * @param  mixed $data
     * @return void
     */
    public function write($id, $data)
    {
        $this->log('write(' . $id . ', ' . $data . ')');
        $session = Sessions::find($id);
        if (is_null($session)) {
            //Create a new session from scratch
            Sessions::Create([
                'id' => $id,
                'data' => $data,
                'timestamp' => time()
            ]);
        } else {
            //Update session data
            $session->data = $data;
            $session->timestamp = time();
            $session->save();
        }
        return true;
    }


    /**
     * create_sid
     * @link https://www.php.net/manual/fr/sessionhandler.create-sid.php
     * @return void
     */
    public function create_sid()
    {
        $sid = parent::create_sid();
        return $sid;
    }


    /**
     * destroy 
     * @link https://www.php.net/manual/fr/sessionhandler.destroy.php
     *
     * @param  mixed $id
     * @return void
     */
    public function destroy($id)
    {
        $this->log('destroy(' . $id . ')');
        $session = Sessions::find($id);
        $session->forceDelete();
        return true;
    }



    /**
     * gc 
     * @link https://www.php.net/manual/fr/sessionhandler.gc.php
     *
     * @param  mixed $maxlifetime
     * @return void
     */
    public function gc($maxlifetime)
    {
        $this->log('gc(' . $maxlifetime . ')');
        Sessions::where('timestamp', '<', time() - intval($maxlifetime))->forceDelete();
        $sessions = Sessions::all();
        $noauthlimit = time() - 30;
        foreach ($sessions as $session) {
            if (!$this->isAuthentified($session->data) && $session->data < $noauthlimit) {
                $session->forceDelete();
            }
        }
        return true;
    }

    /**
     * log
     * Log to a file.
     * @param  string $action
     * @return void
     */
    private function log(string $action)
    {
        if ($this->debug && !is_null($this->logfile)) {
            $base_uri = explode('?', $_SERVER['REQUEST_URI'], 2)[0];
            $hdl = fopen($this->logfile, 'a');
            fwrite($hdl, date('Y-m-d h:i:s') . ' ' . $base_uri . ' : ' . $action . "\n");
            fclose($hdl);
        }
    }
}
