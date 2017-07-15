import java.io.IOException;
import java.util.HashMap;
import java.util.Map;
import java.util.StringTokenizer;

import org.apache.hadoop.fs.Path;
import org.apache.hadoop.io.LongWritable;
import org.apache.hadoop.io.Text;
import org.apache.hadoop.mapreduce.Job;
import org.apache.hadoop.mapreduce.Mapper;
import org.apache.hadoop.mapreduce.Reducer;
import org.apache.hadoop.mapreduce.lib.input.FileInputFormat;
import org.apache.hadoop.mapreduce.lib.input.FileSplit;
import org.apache.hadoop.mapreduce.lib.output.FileOutputFormat;

public class InvertedIndexJob {
	public static void main(String[] args) throws IOException,
			ClassNotFoundException, InterruptedException {
		Job job = new Job();
		job.setJarByClass(InvertedIndexJob.class);
		job.setJobName("Map Reduce Inverted Index Job");

		FileInputFormat.addInputPath(job, new Path(args[0]));
		FileOutputFormat.setOutputPath(job, new Path(args[1]));

		job.setMapperClass(InvertedIndexMapper.class);
		job.setReducerClass(InvertedIndexReducer.class);

		job.setOutputKeyClass(Text.class);
		job.setOutputValueClass(Text.class);
		
		job.waitForCompletion(true);
	}
}

class InvertedIndexMapper extends
		Mapper<LongWritable, Text, Text, Text> {

	private final static Text word = new Text();

	public void map(LongWritable key, Text value, Context context)
			throws IOException, InterruptedException {
		String line = value.toString();
		StringTokenizer itr = new StringTokenizer(line.toLowerCase());
		Text documentId = null;
		if (itr.hasMoreTokens()) {
			documentId = new Text(itr.nextToken());
		}
		while (itr.hasMoreTokens()) {
			word.set(itr.nextToken());
			context.write(word, documentId);
		}
	}
}

class InvertedIndexReducer extends Reducer<Text, Text, Text, Text> {

	public void reduce(Text key, Iterable<Text> values, Context context)
			throws IOException, InterruptedException {
		Map<String, Integer> invertedIndexMap = new HashMap<String, Integer>();
		for (Text value : values) {
			String documentId = value.toString();
			if (invertedIndexMap.containsKey(documentId)) {
				invertedIndexMap.put(documentId, invertedIndexMap.get(documentId) + 1);
			} else {
				invertedIndexMap.put(documentId, 1);
			}
		}
		StringBuffer sb = new StringBuffer();
		for (Map.Entry<String, Integer> entry : invertedIndexMap.entrySet()) {
			sb.append(entry.getKey());
			sb.append(":");
			sb.append(entry.getValue().toString());
			sb.append(" ");
		}
		context.write(key, new Text(sb.toString()));
	}
}
