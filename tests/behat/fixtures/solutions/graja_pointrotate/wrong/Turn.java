public class Turn {
    public static void main(String[] args) {
        int x= 2;
        int y= 3;
        
        // --------------------------------------
        // | Hier fehlt Ihr Code
        // | Verwenden Sie einfache Zuweisungen

        int tmp= y;
        y= x / (y-tmp); // bad
        x= -tmp;

        // | Ende Ihres Codes
        // --------------------------------------
        
        System.out.println(x + " " + y);
	}
}