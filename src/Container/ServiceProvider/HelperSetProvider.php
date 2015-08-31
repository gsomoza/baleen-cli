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
 * <https://github.com/baleen/migrations>.
 */

namespace Baleen\Cli\Container\ServiceProvider;

use Baleen\Cli\Container\Services;
use Baleen\Cli\Helper\ConfigHelper;
use League\Container\ServiceProvider;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;

/**
 * Class HelperSetProvider.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class HelperSetProvider extends ServiceProvider
{
    protected $provides = [
        Services::HELPERSET,
        Services::HELPERSET_QUESTION,
        Services::HELPERSET_CONFIG,
    ];

    /**
     * @inheritdoc
     */
    public function register()
    {
        $container = $this->getContainer();
        $container->singleton(Services::HELPERSET, function () use ($container) {
            $helperSet = new HelperSet();
            $helperSet->set($container->get(Services::HELPERSET_QUESTION), 'question');
            $helperSet->set($container->get(Services::HELPERSET_CONFIG));

            return $helperSet;
        })
            ->withArgument(Services::CONFIG);

        $container->add(Services::HELPERSET_QUESTION, QuestionHelper::class);
        $container->add(Services::HELPERSET_CONFIG, ConfigHelper::class)
            ->withArgument(Services::CONFIG);
    }
}
