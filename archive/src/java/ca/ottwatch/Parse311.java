package ca.ottwatch;

import java.awt.Toolkit;
import java.awt.datatransfer.Clipboard;
import java.awt.datatransfer.ClipboardOwner;
import java.awt.datatransfer.DataFlavor;
import java.awt.datatransfer.StringSelection;
import java.awt.datatransfer.Transferable;
import java.util.ArrayList;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;

import sun.misc.JavaUtilJarAccess;

public class Parse311 {

	public static void main(String[] args) {
		parse311MonthlyCSV();
		// mainLEGACY(args);
	}

	private static void parse311MonthlyCSV() {

		StringBuffer sb = new StringBuffer();
		List<Map<String, String>> data = readDelimited(getClipboardContents());

		for (Map<String, String> d : data) {
			try {
				String created = d.get("creation_date");
				created = normalizeDate(created);

				String callType = d.get("call_type");
				String callDesc = d.get("call_description");
				String wardStr = d.get("ward").toLowerCase();
				int ward = 0;
				if (wardStr.matches(".*ward.*\\d+")) {
					wardStr = wardStr.toLowerCase().replaceAll(".*ward", "");
					wardStr = wardStr.toLowerCase().replaceAll(" ", "");
					ward = Integer.parseInt(wardStr);
				}
				int count = 1;

				String sql = generateSqlInsert(created, callType, callDesc, ward, count);
				sb.append(sql + "\n");
				// System.out.println(sql);
			} catch (Exception e) {
				System.out.println("\n\n" + d + "\n\n");
				e.printStackTrace();
				return;
			}

		}

		System.out.println("Sending to clipboard");
		setClipboardContents(sb.toString());
		System.out.println("DONE");

	}

	private static String normalizeDate(String date) {

		int index = 1;
		date = date.replaceAll("Jan", "" + index++);
		date = date.replaceAll("Feb", "" + index++);
		date = date.replaceAll("Mar", "" + index++);
		date = date.replaceAll("Apr", "" + index++);
		date = date.replaceAll("May", "" + index++);
		date = date.replaceAll("Jun", "" + index++);
		date = date.replaceAll("Jul", "" + index++);
		date = date.replaceAll("Aug", "" + index++);
		date = date.replaceAll("Sep", "" + index++);
		date = date.replaceAll("Oct", "" + index++);
		date = date.replaceAll("Nov", "" + index++);
		date = date.replaceAll("Dec", "" + index++);
		return date;

	}

	public static void mainLEGACY(String[] args) {

		System.out.println("starting...");

		StringBuffer sb = new StringBuffer();

		String contents = getClipboardContents();

		List<Map<String, String>> data = readDelimited(contents);
		for (Map<String, String> d : data) {

			String callType = d.remove("call type");

			callType = callType.trim();
			String callDesc = d.remove("call description");
			callDesc = callDesc.trim();

			String date = "2012-10-01";
			d.remove("total");

			for (String k : d.keySet()) {

				int ward = Integer.parseInt(k);
				String val = d.get(k);
				int count = 0;
				if (val != null && !val.equals("")) {
					val = val.replaceAll(",", "");
					count = Integer.parseInt(val);
				}
				String sql = generateSqlInsert(date, callType, callDesc, ward, count);
				System.out.println(sql);
				sb.append(sql + "\n");
			}
			// System.out.println(d);
		}

		setClipboardContents(sb.toString());

	}

	private static String generateSqlInsert(String date, String callType, String callDesc, int ward, int count) {

		return " insert into data311 (created,type,description,ward,count) values ('" + date + "','" + callType + "','" + callDesc + "'," + ward + "," + count + "); ";

	}

	private static String getClipboardContents() {
		Clipboard clipboard = Toolkit.getDefaultToolkit().getSystemClipboard();
		Transferable contents = clipboard.getContents(null);
		boolean hasTransferableText = (contents != null) && contents.isDataFlavorSupported(DataFlavor.stringFlavor);
		if (!hasTransferableText) {
			/* can't get string content */
			return "";
		}
		try {
			return (String) contents.getTransferData(DataFlavor.stringFlavor);
		} catch (Exception e) {
			throw new RuntimeException(e);
		}
	}

	private static void setClipboardContents(String str) {
		StringSelection stringSelection = new StringSelection(str);
		Clipboard clipboard = Toolkit.getDefaultToolkit().getSystemClipboard();
		clipboard.setContents(stringSelection, new ClipboardOwner() {
			public void lostOwnership(Clipboard arg0, Transferable arg1) {
				/* do nothing */
			}
		});
	}

	private static List<Map<String, String>> readDelimited(String s) {
		String[] lines = s.split("\n");
		List<Map<String, String>> result = new ArrayList<Map<String, String>>();

		String[] keys = lines[0].split("\t");

		for (int x = 0; x < keys.length; x++) {
			keys[x] = keys[x].toLowerCase().trim();
		}

		for (int x = 1; x < lines.length; x++) {
			/* prepare the Map object that represents this row */
			Map<String, String> row = new LinkedHashMap<String, String>();
			result.add(row);

			/*
			 * insert keys in the Map; if data is available from the clipboard then overwrite default '' with the actual value.
			 */
			String[] line = lines[x].split("\t");
			for (int y = 0; y < keys.length; y++) {
				row.put(keys[y], "");
				if (y < line.length) {
					row.put(keys[y], line[y]);
				}
			}
		}

		return result;
	}

}
