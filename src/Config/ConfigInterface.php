<?php

/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Baleen\Cli\Config;

use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Interface ConfigInterface provides a common interface to be extended for applications based on this framework.
 * The resulting class will provide the config configClass (see Symfony's ConfigurationInterface).
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
interface ConfigInterface
{
    /**
     * Returns an array of default values.
     *
     * @return array
     */
    public function getDefaults();

    /**
     * Returns the entire configuration as an array,.
     *
     * @return array
     */
    public function toArray();

    /**
     * Returns the default configuration file name.
     *
     * @return string
     */
    public function getFileName();

    /**
     * Returns an instance of the configuration definition.
     *
     * @return ConfigurationInterface
     */
    public function getDefinition();

    /**
     * Returns an array only with settings that can be configured by the end-user.
     *
     * @return array
     */
    public function getCleanArray();
}
