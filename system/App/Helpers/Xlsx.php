<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         system/App/Helpers/Xlsx.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	namespace App\Helpers;

	defined('BASEPATH') || die('Direct access to this location is not allowed.');

	/** Native Excel export: user text is never interpreted as a formula. */
	class Xlsx
	{
		const MAX_ROWS = 5000;

		public static function save($path, $title, array $headers, iterable $rows, array $parameters = array())
		{
			require_once BASEPATH . '/system/vendor/PHPExcel/PHPExcel.php';
			$book = new \PHPExcel();
			try {
				$sheet = $book->getActiveSheet();
				$sheet->setTitle(mb_substr(preg_replace('/[\\\\\/?*\[\]:]/u', '', (string) $title), 0, 31, 'UTF-8') ?: 'Отчёт');
				self::sheet($sheet, $headers, $rows);
				if ($parameters) { self::sheet($book->createSheet()->setTitle('Параметры'), array('Параметр', 'Значение'), $parameters); }
				$book->setActiveSheetIndex(0);
				\PHPExcel_IOFactory::createWriter($book, 'Excel2007')->save($path);
			} finally { $book->disconnectWorksheets(); }
		}

		protected static function sheet($sheet, array $headers, iterable $rows)
		{
			if (!$headers || count($headers) > 100) { throw new \InvalidArgumentException('Некорректные столбцы XLSX.'); }
			$write = function ($number, array $values) use ($sheet, $headers) {
				foreach (array_values($values) as $column => $value) {
					if ($column >= count($headers)) { break; }
					$type = is_int($value) || is_float($value) ? \PHPExcel_Cell_DataType::TYPE_NUMERIC : \PHPExcel_Cell_DataType::TYPE_STRING;
					if ($type === \PHPExcel_Cell_DataType::TYPE_STRING) {
						$value = preg_replace('/[\x00-\x08\x0b\x0c\x0e-\x1f]/u', '', (string) $value);
						if (mb_strlen($value, 'UTF-8') > 32767) { throw new \RuntimeException('Текст ячейки превышает лимит Excel. Сократите состав отчёта.'); }
					}

					$sheet->setCellValueExplicitByColumnAndRow($column, $number, $value, $type);
				}
			};
			$write(1, $headers);
			$number = 1;
			foreach ($rows as $row) {
				if ($number > self::MAX_ROWS) { throw new \RuntimeException('В XLSX больше 5000 строк. Сократите период или список категорий.'); }
				$write(++$number, $row);
			}

			$last = \PHPExcel_Cell::stringFromColumnIndex(count($headers) - 1);
			$sheet->freezePane('A2');
			$sheet->setAutoFilter('A1:' . $last . $number);
			$sheet->getStyle('A1:' . $last . $number)->getAlignment()->setWrapText(true)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_TOP);
			$sheet->getStyle('A1:' . $last . '1')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
			$sheet->getStyle('A1:' . $last . '1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('2563EB');
			foreach ($headers as $column => $unused) { $sheet->getColumnDimension(\PHPExcel_Cell::stringFromColumnIndex($column))->setWidth(28); }
		}

		public static function download($filename, $title, array $headers, iterable $rows, array $parameters = array())
		{
			$stream = tmpfile();
			if ($stream === false) { throw new \RuntimeException('Не удалось создать временный XLSX.'); }
			try {
				$meta = stream_get_meta_data($stream);
				self::save($meta['uri'], $title, $headers, $rows, $parameters);
				$filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename((string) $filename));
				if (function_exists('apache_setenv')) { @apache_setenv('PUBLIC_NO_CACHE', '1'); }
				$_SERVER['PUBLIC_NO_CACHE'] = '1';
				Request::setHeader('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', true);
				Request::setHeader('Content-Disposition: attachment; filename="' . $filename . '"', true);
				Request::setHeader('Cache-Control: no-store, private, max-age=0', true);
				Request::setHeader('Pragma: no-cache', true);
				Request::setHeader('X-Content-Type-Options: nosniff', true);
				// ZipArchive may replace the file inode; the original tmpfile handle stays empty.
				readfile($meta['uri']);
			} finally { fclose($stream); }
			Request::shutDown();
		}
	}
