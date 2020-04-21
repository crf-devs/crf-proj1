@search
Feature:
    In order to find a user or an asset
    As an organization
    I must be able to search users and assets

    Scenario: As anonymous, I cannot go to the search page
        When I go to "/organizations/201/search?query=foo"
        Then I should be on "/login"
        And the response status code should be 200

    Scenario Outline: As an authenticated parent organization, I can search for a user even in my children
        Given I am authenticated as "john.doe@resop.com"
        And I am on "/organizations/201"
        When I fill in "query" with " <search> "
        And I press "Rechercher"
        Then I should be on "/organizations/201/search"
        And I should see "Rechercher \"<search>\""
        And I should see "<identificationNumber>"
        And I should see "<email>"
        And I should see "Aucun véhicule ne correspond à votre recherche."
        Examples:
            | search         | email              | identificationNumber |
            | jOhN dOe reSOp | john.doe@resop.com | 990001A              |
            | jAnE dOe reSOp | jane.doe@resop.com | 990002A              |

    Scenario: As an admin of a children organization, I can search for a volunteer in my organization
        Given I am authenticated as "jane.doe@resop.com"
        And I am on "/organizations/203"
        When I fill in "query" with " jAnE dOe reSOp "
        And I press "Rechercher"
        Then I should be on "/organizations/203/search"
        And the response status code should be 200
        And I should see "Rechercher \"jAnE dOe reSOp\""
        And I should see "990002A"
        And I should see "jane.doe@resop.com"
        And I should see "Aucun véhicule ne correspond à votre recherche."

    Scenario: As an admin of an organization, I cannot search for a user in another organization
        Given I am authenticated as "jane.doe@resop.com"
        And I am on "/organizations/203"
        When I fill in "query" with " cHuCk nOrRiS reSOp "
        And I press "Rechercher"
        Then I should be on "/organizations/203/search"
        And the response status code should be 200
        And I should see "Rechercher \"cHuCk nOrRiS reSOp\""
        And I should see "Aucun bénévole ne correspond à votre recherche."
        And I should see "Aucun véhicule ne correspond à votre recherche."

    Scenario Outline: As an admin of a parent organization, I can search for an asset even in my children
        Given I am authenticated as "john.doe@resop.com"
        And I am on "/organizations/201"
        When I fill in "query" with " <search> "
        And I press "Rechercher"
        Then I should be on "/organizations/201/search"
        And the response status code should be 200
        And I should see "Rechercher \"<search>\""
        And I should see "<name>"
        And I should see "<type>"
        And I should see "Aucun bénévole ne correspond à votre recherche."
        Examples:
            | search | name   | type |
            | 75992  | 75992  | VPSP |
            | 75012  | 75012  | VPSP |

    Scenario: As an admin of a children organization, I can search for a user in my organization
        Given I am authenticated as "jane.doe@resop.com"
        And I am on "/organizations/203"
        When I fill in "query" with " 75012 "
        And I press "Rechercher"
        Then I should be on "/organizations/203/search"
        And the response status code should be 200
        And I should see "Rechercher \"75012\""
        And I should see "VPSP"
        And I should see "75012"
        And I should see "Aucun bénévole ne correspond à votre recherche."

    Scenario: As an admin of an organization, I cannot search for a user in another organization
        Given I am authenticated as "jane.doe@resop.com"
        And I am on "/organizations/203"
        When I fill in "query" with " 77282 "
        And I press "Rechercher"
        Then I should be on "/organizations/203/search"
        And the response status code should be 200
        And I should see "Rechercher \"77282\""
        And I should see "Aucun bénévole ne correspond à votre recherche."
        And I should see "Aucun véhicule ne correspond à votre recherche."
