import java.io.BufferedReader;
import java.io.File;
import java.io.FileNotFoundException;
import java.io.FileReader;
import java.io.FileWriter;
import java.io.IOException;
import java.io.PrintWriter;
import java.text.ParseException;
import java.util.HashMap;
import java.util.HashSet;
import java.util.Map;
import java.util.Set;

import org.jsoup.Jsoup;
import org.jsoup.nodes.Document;
import org.jsoup.nodes.Element;
import org.jsoup.select.Elements;



public class ExtractWords {
		
		public static final String OUTPUT_FILE = "big.txt";
		public static final String CSV = "/home/vishal/Documents/Vishal/data/mapNYTimesDataFile.csv";
		public static final String INPUT_DIR = "/home/vishal/Documents/Vishal/data/NYTimesDownloadData/";
		
		public static void main(String[] args) throws ParseException, IOException {
		    
		    BufferedReader br = new BufferedReader(new FileReader(CSV));
		    String line =  null;
		    HashMap<String,String> fileUrlmap = new HashMap<String, String>();
		    HashMap<String,String> urlFilemap = new HashMap<String, String>();

		    while((line = br.readLine())!=null){
		        String str[] = line.split(",");
		            fileUrlmap.put(str[0], str[1]);
		            urlFilemap.put(str[1], str[0]);
		        }
		    br.close();
		
		File dir = new File(INPUT_DIR);
		Set<String> edges = new HashSet<String>(); 
		for(File file:dir.listFiles()){
			Document doc = Jsoup.parse(file, "UTF-8", fileUrlmap.get(file.getName()));
			Elements Paragraphs = doc.select("body");
			Elements Titles = doc.select("title");
			for(Element p:Paragraphs){
					edges.add(p.text());	
			}
			for(Element t:Titles){
					edges.add(t.text());	
			}
		}
			
		
		PrintWriter writer = new PrintWriter(new FileWriter(OUTPUT_FILE));
		for(String s:edges){
			writer.println(s);
		}
		writer.flush();
		writer.close();
		System.out.println("Finished");
	}
}
		

