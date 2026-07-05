package therp.print;

import javax.swing.JApplet;
import java.awt.Graphics;
import javax.swing.JLabel;
import java.util.TimerTask;
import java.util.Timer;
import java.awt.Color;
import javax.swing.SwingConstants;

public class PrintApplet extends JApplet 
{
	private JLabel label = new JLabel();
	private Timer timer = new Timer();
	
	public void init()
	{		
		setBackground(Color.WHITE);	
		add(label);
		label.setHorizontalAlignment(SwingConstants.CENTER);
		label.setText("Printing receipt...");	
		timer.schedule(new MyTimerTask(), 5000L);
	}
	
	private class MyTimerTask extends TimerTask	
	{
		public void run()
		{
			label.setText("Printing done.");
		}
	}
}
