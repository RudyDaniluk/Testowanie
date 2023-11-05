import java.util.Calendar;

public class Player {

    private String name;
    private Integer bornYear;
    private Boolean agree;

    public Player(Boolean agree, String name, Integer bornYear) {
        this.agree = agree;
        this.name = validateName(name);
        this.bornYear = validateBornYear(bornYear);
    }


    public Boolean createPlayer(Boolean agree, String name, Integer bornYear) {
        this.agree = agree;
        this.name = validateName(name);
        this.bornYear = validateBornYear(bornYear);
        return true;
    }



    public void setName(String name) {
        this.name = validateName(name);
    }

    public void setBornYear(Integer bornYear) {
        this.bornYear = validateBornYear(bornYear);
    }

    public Boolean getAgree(){
        return this.agree;
    }

    public Integer getBornYear(){
        return this.bornYear;
    }

    public String getName(){
        return this.name;
    }

    private String validateName(String name) {
        if (name.matches("^[a-zA-Z0-9_-]+$")) {
            return name;
        } else {
            throw new IllegalArgumentException("Invalid player name");
        }
    }

    private Integer validateBornYear(Integer bornYear) {
        int currentYear = Calendar.getInstance().get(Calendar.YEAR);
        if (currentYear - bornYear >= 18) {
            return bornYear;
        } else {
            throw new IllegalArgumentException("Player must be at least 18 years old");
        }
    }
}