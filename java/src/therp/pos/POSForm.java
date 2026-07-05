package therp.pos;

import java.awt.BorderLayout;
import java.awt.FlowLayout;
import java.awt.event.ActionEvent;
import java.awt.event.ActionListener;
import java.awt.event.KeyEvent;
import java.net.URL;
import java.text.DateFormat;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.Iterator;
import java.util.List;
import java.util.StringTokenizer;

import javax.swing.ImageIcon;
import javax.swing.JButton;
import javax.swing.JComboBox;
import javax.swing.JFrame;
import javax.swing.JLabel;
import javax.swing.JMenu;
import javax.swing.JMenuBar;
import javax.swing.JMenuItem;
import javax.swing.JPanel;
import javax.swing.JScrollPane;
import javax.swing.JTable;
import javax.swing.KeyStroke;
import javax.swing.UIManager;
import javax.swing.event.TableModelListener;
import javax.swing.table.TableColumnModel;
import javax.swing.table.TableModel;

import therp.util.Context;
import therp.util.Request;
import therp.util.TableLayoutPanel;
import therp.util.TableLayoutPanel.Cell;
import therp.util.TableLayoutPanel.Row;

public class POSForm implements ActionListener
{
    private int orderId = -1;
    private JLabel orderIdLabel;
    private JComboBox locationCombo;
    private JLabel dateLabel;
    private Date orderDate;
    private DateFormat dateFormatter;
    private List orderLines;
    private JLabel messLabel;
    
    public static void main(String args[])
        throws Exception
    {
        Context.host = "localhost";
        Context.path = "therp";
        Context.user = "frebe";
        Context.pwd = "abc123";
        new POSForm(null);
    }
    
    public POSForm(String orderid)
        throws Exception
    {
        
        UIManager.setLookAndFeel(UIManager.getSystemLookAndFeelClassName());        
        JFrame frame = new JFrame("thERP - POS");
        frame.setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
        
        JMenuBar menuBar = new JMenuBar();
        frame.setJMenuBar(menuBar);
        JMenu menu = new JMenu(tr("File"));
        menuBar.add(menu);
        JMenuItem item = new JMenuItem(tr("New"));
        item.setActionCommand("new");
        menu.add(item);
        item = new JMenuItem(tr("Open"));
        menu.add(item);
        item = new JMenuItem(tr("Save"));
        item.setActionCommand("save");
        item.addActionListener(this);
        item.setAccelerator(KeyStroke.getKeyStroke(KeyEvent.VK_S, ActionEvent.CTRL_MASK));
        menu.add(item);
        
        JPanel mainPanel = new JPanel();
        frame.setContentPane(mainPanel);
        mainPanel.setLayout(new BorderLayout());
        TableLayoutPanel detailPanel = new TableLayoutPanel();
        mainPanel.add(detailPanel, BorderLayout.NORTH);
        detailPanel.createColumn(10);
        detailPanel.createColumn();
        detailPanel.createColumn(10);
        detailPanel.createColumn();
        detailPanel.createColumn(10);
        Row row = detailPanel.createRow(10);
        row = detailPanel.createRow();        
        row.createCell();
        row.createCell(new JLabel(tr("Order id") + ":"));
        row.createCell();
        orderIdLabel = new JLabel();
        row.createCell(orderIdLabel);
        row = detailPanel.createRow();
        row.createCell();
        row.createCell(new JLabel(tr("Location") + ":"));
        row.createCell();
        locationCombo = new JComboBox();
        Request request = createRequest();
        request.setAction("getlocations");
        populateCombo(locationCombo, request.get());
        row.createCell(locationCombo);
        row = detailPanel.createRow();        
        row.createCell();
        row.createCell(new JLabel(tr("Order date") + ":"));
        row.createCell();
        dateFormatter = new SimpleDateFormat("yy-MM-dd");
        orderDate = new Date();
        dateLabel = new JLabel(dateFormatter.format(orderDate));
        row.createCell(dateLabel);
        row = detailPanel.createRow();
        messLabel = new JLabel();
        Cell cell = row.createCell(messLabel);
        cell.setColumnSpan(3);
        
        detailPanel.createRow(10);
        
        orderLines = new ArrayList();
        JTable table = new JTable(new ThisTableModel());
        TableColumnModel colModel = table.getColumnModel();
        colModel.getColumn(0).setPreferredWidth(10);
        colModel.getColumn(1).setPreferredWidth(50);
        colModel.getColumn(2).setPreferredWidth(20);
        colModel.getColumn(3).setPreferredWidth(20);
        colModel.getColumn(4).setPreferredWidth(20);
        mainPanel.add(new JScrollPane(table), BorderLayout.CENTER);
        
        JPanel buttonRow = new JPanel();
        mainPanel.add(buttonRow, BorderLayout.SOUTH);
        buttonRow.setLayout(new FlowLayout());
        JButton button = new JButton("Save");
        buttonRow.add(button);
        button.setActionCommand("save");
        button.addActionListener(this);
        button = new JButton("Invoice");
        buttonRow.add(button);
        button.setActionCommand("invoice");
        button.addActionListener(this);
        button = new JButton("Pay");
        buttonRow.add(button);
        button.setActionCommand("pay");
        button.addActionListener(this);
        
        
        frame.pack();
        frame.setVisible(true);
    }
    
    private void load()
        throws Exception
    {
        Request request = createRequest();
        request.setAction("getsalesorder");
        request.setParam("orderid", orderId);
        Iterator i = request.get();
        String header = (String) i.next();
        StringTokenizer htok = new StringTokenizer(header, ";");
        String customerId = htok.nextToken();
        String customerName = htok.nextToken();
        String locationId = htok.nextToken();
        setSelectedValue(locationCombo, locationId);
        
        while (i.hasNext()) {
            String line = (String) i.next();
            StringTokenizer ltok = new StringTokenizer(line, ";");
            String no = ltok.nextToken();
            String productId = ltok.nextToken();
            String model = ltok.nextToken();
            String quantity = ltok.nextToken();
            String unitPrice = ltok.nextToken();            
        }        
    }
    
    private void populateCombo(JComboBox combo, Iterator i)
        throws Exception
    {
        combo.removeAllItems();
        while (i.hasNext()) {
            String line = (String) i.next();
            System.out.println(line);
            StringTokenizer tok = new StringTokenizer(line, ";");
            String value = tok.nextToken();
            String text = tok.nextToken();
            combo.addItem(new ComboItem(value, text));
        }
    }
    
    private void setSelectedValue(JComboBox combo, String value)
    {
        for (int i=0; i < combo.getItemCount(); i++) {
            ComboItem item = (ComboItem) combo.getItemAt(i);
            if (item != null) {
                if (item.getValue() == value)
                    combo.setSelectedIndex(i);
            }
        }
    }
    
    private class ComboItem
    {
        private String value;
        private String text;
        
        public ComboItem(String value, String text)
        {
            this.value = value;
            this.text = text;
        }
        
        public String getValue()
        {
            return value;
        }
        
        public String toString()
        {
            return text;
        }
    }
    
    private class ThisTableModel
        implements TableModel
    {
        private ImageIcon delIcon;
        private JButton findButton;
        
        public ThisTableModel()
        {
            URL url = this.getClass().getResource("/therp/images/delete.png");
            System.out.println(url);
            delIcon = new ImageIcon(url);
            findButton = new JButton(tr("Find"));
            findButton.setActionCommand("find");
            findButton.addActionListener(POSForm.this);
        }
        
        public void addTableModelListener(TableModelListener l) {
            
        }

        public Class<?> getColumnClass(int columnIndex) {
            switch (columnIndex) {
            case 0: return ImageIcon.class;
            case 1: return String.class;
            case 2: return JButton.class;
            case 3: return Double.class;
            }
            return String.class;
        }

        public int getColumnCount() {
            return 6;
        }

        public String getColumnName(int columnIndex) {
            switch (columnIndex) {
            case 0: return tr("Del"); 
            case 1: return tr("Product");
            case 2: return "";
            case 3: return tr("Quantity"); 
            case 4: return tr("Unit price"); 
            case 5: return tr("Amount"); 
            }
            return null;
        }

        public int getRowCount() {
            return orderLines.size() + 1;
        }

        public Object getValueAt(int rowIndex, int columnIndex) {
            if (rowIndex < orderLines.size()) {
                OrderLine line = (OrderLine) orderLines.get(rowIndex);
                switch (columnIndex) {
                case 0: return delIcon;
                case 1: return line.productId;
                case 2: return findButton;
                case 3: return line.quantity;
                }
            }
            return null;
        }

        public boolean isCellEditable(int rowIndex, int columnIndex) {
            switch (columnIndex) {
            case 0: return false;
            }
            return true;
        }

        public void removeTableModelListener(TableModelListener l) {
            
        }

        public void setValueAt(Object aValue, int rowIndex, int columnIndex) 
        {
            OrderLine line;
            if (rowIndex < orderLines.size()) {
                line = (OrderLine) orderLines.get(rowIndex);                
            } else {
                line = new OrderLine();
                orderLines.add(line);
            }
            switch (columnIndex) {
            case 1: line.productId = (String) aValue; break;
            case 2: 
                if (aValue != null) 
                    line.quantity = (Double) aValue; 
                break;
            }
            line.dirty = true;                  
        }    
    }
    
    private void newOrder()
    {
        
    }
    
    private void saveOrder()
        throws Exception
    {
    	System.err.println("Save");
        Request request = createRequest();
        request.setAction("savesalesorder");
        request.setParam("orderid", orderId);
        ComboItem item = (ComboItem) locationCombo.getSelectedItem();        
        request.setParam("locationid", item.getValue());
        for (int i=0; i < orderLines.size(); i++) {            
            OrderLine line = (OrderLine) orderLines.get(i);
            if (line.dirty) {
            	System.err.println("Save no " + i);
                request.setParam("no_" + i, line.no);
                request.setParam("productid_" + i, line.productId);            
                request.setParam("quantity_" + i, line.quantity);
            }
        }
        request.setParam("count", orderLines.size());
        orderId = Integer.parseInt(request.post());
        orderIdLabel.setText("" + orderId);
        for (int i=0; i < orderLines.size(); i++) {
            OrderLine line = (OrderLine) orderLines.get(i);
            line.dirty = false;
        }
    }
    
    private Request createRequest()
    {
        return new Request("sales/posserver.php");
    }
        
    public void actionPerformed(ActionEvent event)
    {
        try {
            if ("new".equals(event.getActionCommand())) {
                newOrder();
                return;
            }
            if ("save".equals(event.getActionCommand())) {
                saveOrder();
            }
        } catch (Throwable e) {
            e.printStackTrace();
        }
    }
    
    private class OrderLine
    {
        public int no = -1;
        public String productId;
        public double quantity;  
        public boolean dirty = false;
    }
    
    private String tr(String text)
    {
        return text;
    }

}
