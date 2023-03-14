<?php

namespace App\Services;

use Faker\Factory;
use Faker\Generator;
use Nette\Utils\Json;

class DocumentGenerator
{
	private Generator $f;

	public function __construct()
	{
		$this->f = Factory::create();
	}

	public function get_small(): array
	{
		return [
			'trivial' => 'object'
		];
	}

	/**
	 * nice-to-have: dynamic depth passed in, cache the generated array for performance
	 */
	public function get_deep(): array
	{
		return [
			'it' => [
				'is' => [
					'turtles' => [
						'all' => [
							'the' => [
								'way' => [
									'down' => 'it is turtles all the way down'
								]
							]
						]
					]
				]
			]
		];
	}

	public function get_large()
	{
		$data = Json::decode(<<<'EOF'
					{
					  "insertId": "3vvsa9fev2zwn",
					  "jsonPayload": {
					    "meta": {
					      "request": {
					        "ip": "35.191.12.178",
					        "ua": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36"
					      },
					      "typeDefinition": {
					        "name": "settlement_mark_as_paid",
					        "class": "App\\Core\\AuditLogging\\Type\\Settlement\\SettlementMarkAsPaid",
					        "version": 1
					      }
					    },
					    "message": "Settlement #5530 has been marked as paid by XYZ",
					    "data": {
					      "settlement": {
					        "total": 23420,
					        "paidDate": "2021-02-08T00:00:00+00:00",
					        "notSoldLots": "{}",
					        "vatApplies": 1,
					        "version": 3,
					        "id": 5530,
					        "dueDate": "2021-01-26T00:00:00+00:00",
					        "locked": true,
					        "issuedDate": "2021-12-09T00:00:00+00:00"
					      },
					      "loggedBy": {
					        "id": 124,
					        "label": "XYZ UKC",
					        "type": "admin"
					      }
					    }
					  },
					  "resource": {
					    "type": "global",
					    "labels": {
					      "project_id": "application-xyz-prod"
					    }
					  },
					  "timestamp": "2021-02-08T17:16:31.163896Z",
					  "severity": "INFO",
					  "labels": {
					    "environment": "production",
					    "log_type": "audit-log",
					    "app": "application-xyz",
					    "tenant": "871a45d8-35b0-4903-8f39-6f44048c8527"
					  },
					  "logName": "projects/application-xyz-prod/logs/application-xyz-prod",
					  "receiveTimestamp": "2021-02-08T17:16:31.191400257Z"
					}
					EOF, Json::FORCE_ARRAY);

		return $data;
	}

	public function get_large_dynamic()
	{
		$data = <<<EOF
					{
					  "insertId": "{$this->f->uuid}",
					  "jsonPayload": {
					    "meta": {
					      "request": {
					        "ip": "{$this->f->ipv4}",
					        "ua": "{$this->f->internetExplorer}"
					      },
					      "typeDefinition": {
					        "name": "{$this->f->word}",
					        "class": "value",
					        "version": 1
					      }
					    },
					    "message": "{$this->f->word}",
					    "data": {
					      "settlement": {
					        "total": {$this->f->numerify("####")},
					        "paidDate": "{$this->f->dateTimeThisYear->format("c")}",
					        "notSoldLots": "{}",
					        "vatApplies": 1,
					        "version": 3,
					        "id": {$this->f->numerify("####")},
					        "dueDate": "{$this->f->dateTimeThisYear->format("c")}",
					        "locked": true,
					        "issuedDate": "{$this->f->dateTimeThisYear->format("c")}"
					      },
					      "loggedBy": {
					        "id": {$this->f->numerify("####")},
					        "label": "{$this->f->name}",
					        "type": "{$this->f->word}"
					      }
					    }
					  },
					  "resource": {
					    "type": "global",
					    "labels": {
					      "project_id": "project"
					    }
					  },
					  "timestamp": "{$this->f->dateTimeThisYear->format("c")}",
					  "severity": "INFO",
					  "labels": {
					    "environment": "production",
					    "log_type": "audit-log",
					    "app": "application",
					    "tenant": "{$this->f->uuid}"
					  },
					  "logName": "projects/application/logs/production",
					  "receiveTimestamp": "{$this->f->dateTimeThisYear->format("c")}"
					}
					EOF;

		return Json::decode($data, Json::FORCE_ARRAY);
	}

}
