<?php

namespace WebImage\WpCli\HackScanner;

use App\WpCli\Application;
use GuzzleHttp\RequestOptions;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use WebImage\Application\AbstractCommand;
use WebImage\Application\ApplicationInterface;

class CheckRedirectCommand extends AbstractCommand {
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->assertUrl($output, $url = $input->getArgument('url'));

		return $this->checkRedirect($input, $output, $url);
	}

	private function assertUrl(OutputInterface $output, string $url)
	{
		if (!preg_match('#https?://.+#', $url) !== false) {
			$output->writeln('URL must start with http:// or https://');
			exit(-1);
		}
	}

	private function checkRedirect(InputInterface $input, OutputInterface $output, string $url)
	{
		$client = new \GuzzleHttp\Client();

		$response = $client->get($url, [
			RequestOptions::ALLOW_REDIRECTS => $output->isVeryVerbose() ? ['max' => 10, 'strict' => true, 'track_redirects' => true] : false,
		]);

		$output->writeln('GET <href='.$url.'>' . $url  . '</> => ' . $response->getStatusCode());

		if ($output->isVerbose()) {
			foreach($response->getHeader('Location') as $location) {
				$output->writeln('  - Location: ' . $location);
			}
		}

		if ($output->isVeryVerbose()) {
			$redirectUriHistory = $response->getHeader('X-Guzzle-Redirect-History'); // retrieve Redirect URI history
			$redirectCodeHistory = $response->getHeader('X-Guzzle-Redirect-Status-History');

			array_unshift($redirectUriHistory, $url);
			array_push($redirectCodeHistory, $response->getStatusCode());

			for ($i = 0, $j = count($redirectUriHistory); $i < $j; $i++) {
				$output->writeln('  - GET <href=' . $redirectUriHistory[$i] . '>' . $redirectUriHistory[$i] . '</> => ' . $redirectCodeHistory[$i]);
			}
		}

		return $output->isQuiet() ? (in_array($response->getStatusCode(), [301, 302]) ? 1 : 0) : 0;
	}
	
	private function getApp(): ApplicationInterface
	{
		return $this->getContainer()->get(ApplicationInterface::class);
	}

	protected function configure()
	{
		parent::configure();
		$this->setDescription('Check a remote URL for redirection headers')
			->addArgument('url', InputArgument::REQUIRED, 'The URL to check');
			//Not working->addOption('return', 'r', InputOption::VALUE_NONE, 'Return code = 1 for redirect and 0 for no redirect');
	}
}