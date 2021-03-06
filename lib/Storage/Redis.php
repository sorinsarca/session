<?php
/* ===========================================================================
 * Opis Project
 * http://opis.io
 * ===========================================================================
 * Copyright 2013 Marius Sarca
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ============================================================================ */

namespace Opis\Session\Storage;

use SessionHandlerInterface;
use Predis\Client;
use Opis\Session\SessionStorage;

class Redis extends SessionStorage implements SessionHandlerInterface
{
 
    protected $prefix;
    
    protected $maxLifetime;
    
    protected $redis;
    
    /**
     * Constructor
     *
     * @access public
     * 
     */
    
    public function __construct(Client $redis, $prefix = 'session_', $maxLifetime = 0)
    {
        $this->redis = $redis;
        $this->prefix = $prefix;
        $this->maxLifetime = $maxLifetime > 0 ? $maxLifetime : ini_get('session.gc_maxlifetime');
    }
    
    /**
     * Destructor.
     *
     * @access public
     */

    public function __destruct()
    {
        parent::__destruct();
        
        session_write_close();
        
        $this->redis = null;
    }


    /**
     * Open session.
     *
     * @access  public
     * @param   string   $savePath     Save path
     * @param   string   $sessionName  Session name
     * @return  boolean
     */

    public function open($savePath, $sessionName)
    {
        return true;
    }

    /**
     * Close session.
     *
     * @access  public
     * @return  boolean
     */

    public function close()
    {
        return true;
    }

    /**
     * Returns session data.
     *
     * @access  public
     * @param   string  $id  Session id
     * @return  string
     */

    public function read($id)
    {
        return (string) $this->redis->get($this->prefix . $id);
    }

    /**
     * Writes data to the session.
     *
     * @access  public
     * @param   string  $id    Session id
     * @param   string  $data  Session data
     */

    public function write($id, $data)
    {
        $this->redis->set($this->prefix . $id, $data);
        
        $this->redis->expire($this->prefix . $id, $this->maxLifetime);
        
        return true;
    }

    /**
     * Destroys the session.
     *
     * @access  public
     * @param   string   $id  Session id
     * @return  boolean
     */

    public function destroy($id)
    {
        return (bool) $this->redis->delete($this->prefix . $id);
    }

    /**
     * Garbage collector.
     *
     * @access  public
     * @param   int      $maxLifetime  Lifetime in secods
     * @return  boolean
     */

    public function gc($maxLifetime)
    {
        return true;
    }
}
